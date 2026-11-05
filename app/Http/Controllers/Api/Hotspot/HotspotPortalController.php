<?php

namespace App\Http\Controllers\Api\Hotspot;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;
use App\Integration\MikroTik\Services\RouterOSService;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class HotspotPortalController extends Controller
{
    /**
     * Helper to return standard JSON with CORS headers
     */
    private function jsonResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /**
     * 1. GET /api/v1/hotspot/packages
     * Returns list of hotspot, voucher, pppoe packages from ServiceProfile
     */
    public function packages(Request $request): JsonResponse
    {
        try {
            $type = strtolower(trim((string) $request->input('type', 'all')));

            $query = ServiceProfile::withoutGlobalScopes();

            // Status filter: only active or profiles without status column set
            $query->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 'active');
            });

            // Hanya paket yang diizinkan tampil di Landing Page / Login Page
            $query->where(function ($q) {
                $q->where('show_on_landing', true)
                  ->orWhere('show_on_landing', 1);
            });

            if ($type === 'hotspot') {
                $query->where(function ($q) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(service_type)'), ['hotspot', 'member_hotspot'])
                      ->orWhere('service_type', 'like', '%hotspot%');
                });
            } elseif ($type === 'voucher') {
                $query->where(function ($q) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(service_type)'), ['voucher'])
                      ->orWhere('service_type', 'like', '%voucher%');
                });
            } elseif ($type === 'pppoe') {
                $query->where(function ($q) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(service_type)'), ['pppoe', 'ftth', 'kombinasi'])
                      ->orWhere('service_type', 'like', '%pppoe%')
                      ->orWhere('service_type', 'like', '%ftth%');
                });
            } else {
                // all
                $query->where(function ($q) {
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(service_type)'), ['hotspot', 'voucher', 'pppoe', 'ftth', 'kombinasi', 'member_hotspot'])
                      ->orWhere('service_type', 'like', '%hotspot%')
                      ->orWhere('service_type', 'like', '%voucher%')
                      ->orWhere('service_type', 'like', '%pppoe%')
                      ->orWhere('service_type', 'like', '%ftth%')
                      ->orWhereNull('service_type');
                });
            }

            $profiles = $query->orderBy('base_price', 'asc')->get();

            $packages = $profiles->map(function ($profile) {
                // Formatting time & active period (Jam, Hari, atau Bulan sesuai yang dibuat)
                $vUnit = strtolower((string)($profile->validity_unit ?? ''));
                $vDays = (int)($profile->validity_days ?? 0);
                $vHours = (int)($profile->validity_hours ?? 0);
                $dVal = (int)($profile->duration_value ?? 0);
                $dUnit = strtolower((string)($profile->duration_unit ?? ''));

                $activePeriod = '-';
                if ($vUnit === 'months' || ($vDays > 0 && $vDays % 30 === 0 && $vDays >= 30 && empty($vHours))) {
                    $months = $vDays > 0 ? round($vDays / 30) : ($dVal > 0 ? $dVal : 1);
                    $activePeriod = $months . ' Bulan';
                } elseif ($vUnit === 'days' || $vDays > 0) {
                    $activePeriod = $vDays . ' Hari';
                } elseif ($vUnit === 'hours' || $vHours > 0) {
                    $activePeriod = $vHours . ' Jam';
                } elseif ($dVal > 0) {
                    $unitLabel = ($dUnit === 'months') ? 'Bulan' : (($dUnit === 'days') ? 'Hari' : 'Jam');
                    $activePeriod = $dVal . ' ' . $unitLabel;
                } else {
                    $activePeriod = '30 Hari';
                }
                $timeLimit = $activePeriod;

                // Kuota: Harus Unlimited kecuali jika package_type secara eksplisit quota_based dengan kuota positif
                $pkgType = strtolower((string)($profile->package_type ?? 'unlimited'));
                $quotaLimit = 'Unlimited';
                if ($pkgType === 'quota_based' && !empty($profile->quota_value) && $profile->quota_value > 0) {
                    $quotaLimit = $profile->quota_value . ' ' . ($profile->quota_unit ?? 'MB');
                }

                // Kecepatan: Wajib ada satuan Mbps
                $dl = $profile->download_speed;
                $up = $profile->upload_speed;
                $speed = '10 Mbps';
                if (!empty($dl) || !empty($up)) {
                    $dlNum = ($dl >= 1000) ? round($dl / 1000, 1) : $dl;
                    $upNum = ($up >= 1000) ? round($up / 1000, 1) : $up;
                    if ($dlNum && $upNum && $dlNum != $upNum) {
                        $speed = $dlNum . ' Mbps / ' . $upNum . ' Mbps';
                    } elseif ($dlNum) {
                        $speed = $dlNum . ' Mbps';
                    } elseif ($upNum) {
                        $speed = $upNum . ' Mbps';
                    }
                }

                $price = (int) ($profile->promo_price > 0 ? $profile->promo_price : ($profile->base_price ?? $profile->cost_price ?? 0));

                $rawType = strtolower((string)($profile->service_type ?? 'voucher'));
                $normalizedType = 'voucher';
                if (str_contains($rawType, 'hotspot')) {
                    $normalizedType = 'hotspot';
                } elseif (str_contains($rawType, 'pppoe') || str_contains($rawType, 'ftth') || str_contains($rawType, 'kombinasi')) {
                    $normalizedType = 'pppoe';
                }

                return [
                    'id' => $profile->id,
                    'profile_name' => $profile->name,
                    'service_type' => $normalizedType,
                    'price_sell' => $price,
                    'shared' => (int) ($profile->max_devices ?? $profile->radius_simultaneous_use ?? 1),
                    'time_limit' => $timeLimit,
                    'active_period' => $activePeriod,
                    'quota_limit' => $quotaLimit,
                    'speed' => $speed,
                    'description' => $profile->description ?? '',
                    'buy_url' => url('/buy-voucher?paket=' . $profile->id),
                ];
            });

            return $this->jsonResponse([
                'code' => 200,
                'status' => 'success',
                'count' => $packages->count(),
                'data' => $packages,
            ]);
        } catch (Throwable $e) {
            Log::error('Hotspot packages error: ' . $e->getMessage());
            return $this->jsonResponse([
                'code' => 500,
                'status' => 'error',
                'msg' => 'Gagal memuat paket hotspot: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * 2. GET /api/v1/hotspot/check-user
     * Check user status, active devices, quota & expiry
     */
    public function checkUser(Request $request): JsonResponse
    {
        $unique = trim((string)($request->input('unique') ?? $request->input('username') ?? ''));

        if ($unique === '') {
            return $this->jsonResponse([
                'code' => 400,
                'status' => 'error',
                'msg' => 'Username atau kode voucher wajib diisi.',
            ], 400);
        }

        try {
            // 1. Cari data user / voucher / pppoe
            $hotspotUser = HotspotUser::withoutGlobalScopes()->where('username', $unique)->with('serviceProfile')->first();
            $voucher = null;
            $customerService = null;
            $pppoeUser = null;

            if (!$hotspotUser) {
                $voucher = Voucher::withoutGlobalScopes()->where('code', $unique)->with('serviceProfile')->first();
            }

            if (!$hotspotUser && !$voucher) {
                $pppoeUser = PPPoEUser::withoutGlobalScopes()->where('username', $unique)->with(['customerService.customer', 'serviceProfile'])->first();
            }

            if (!$hotspotUser && !$voucher && !$pppoeUser) {
                $customerService = CustomerService::withoutGlobalScopes()->where('username', $unique)->with(['customer', 'serviceProfile'])->first();
            }

            // Fallback: Jika input berupa ID Pelanggan (code), No HP, atau Email di tabel Customer
            $customer = null;
            if (!$hotspotUser && !$voucher && !$pppoeUser && !$customerService) {
                $uniqueLower = strtolower($unique);
                $customer = Customer::withoutGlobalScopes()
                    ->where(function($q) use ($unique, $uniqueLower) {
                        $q->whereRaw('LOWER(code) = ?', [$uniqueLower])
                          ->orWhere('phone', $unique)
                          ->orWhereRaw('LOWER(email) = ?', [$uniqueLower]);
                    })
                    ->with(['customerServices.serviceProfile', 'customerServices.pppoeUser', 'customerServices.hotspotUser'])
                    ->first();

                if ($customer) {
                    $customerService = $customer->customerServices?->first();
                    $pppoeUser = $customerService?->pppoeUser;
                    $hotspotUser = $customerService?->hotspotUser;
                }
            } else {
                $customer = $customerService?->customer ?? $pppoeUser?->customerService?->customer ?? $hotspotUser?->customerService?->customer ?? null;
            }

            // Bersihkan ghost sessions yang tidak memiliki IP dan MAC sama sekali
            try {
                HotspotActiveSession::where(function($q) {
                    $q->whereNull('address')->orWhere('address', '')->orWhere('address', '-')->orWhere('address', '0.0.0.0');
                })->where(function($q) {
                    $q->whereNull('mac_address')->orWhere('mac_address', '')->orWhere('mac_address', '-');
                })->delete();

                OnlineSession::where(function($q) {
                    $q->whereNull('address')->orWhere('address', '')->orWhere('address', '-')->orWhere('address', '0.0.0.0');
                })->where(function($q) {
                    $q->whereNull('mac_address')->orWhere('mac_address', '')->orWhere('mac_address', '-');
                })->where(function($q) {
                    $q->whereNull('caller_id')->orWhere('caller_id', '')->orWhere('caller_id', '-');
                })->delete();
            } catch (\Throwable $e) {
                // Ignore cleanup errors
            }

            // Kumpulan candidate username untuk pengecekan sesi aktif di MikroTik/Radius
            $candidateUsernames = array_values(array_unique(array_filter([
                $unique,
                $customerService?->username,
                $pppoeUser?->username,
                $hotspotUser?->username,
                $voucher?->code,
            ])));

            // 2. Ambil sesi aktif dari semua sumber (Hotspot, PPPoE, OnlineSession, RadiusAccounting)
            $activeHsSessions = HotspotActiveSession::whereIn('user', $candidateUsernames)->with('router')->get();
            $activePppSessions = PppActiveSession::whereIn('name', $candidateUsernames)->with('router')->get();
            $onlineSessions = OnlineSession::whereIn('username', $candidateUsernames)->get();
            $radAccts = RadiusAccounting::whereIn('username', $candidateUsernames)->whereNull('acct_stop_time')->latest('acct_start_time')->get();

            $devices = [];
            $deviceLines = [];
            $index = 1;

            // Helper untuk membersihkan IP dan MAC
            $resolveDevice = function ($rawIp, $rawMac, $rawUptime, $rawBytesIn = 0, $rawBytesOut = 0, $rawId = null) use (&$devices, &$deviceLines, &$index, $onlineSessions, $radAccts) {
                $ip = trim((string)($rawIp ?? ''));
                $mac = trim((string)($rawMac ?? ''));
                $uptime = trim((string)($rawUptime ?? ''));

                if (($ip === '' || $ip === '-' || $ip === '0.0.0.0') && $onlineSessions->isNotEmpty()) {
                    $ip = trim((string)($onlineSessions->first()->address ?? ''));
                }
                if (($mac === '' || $mac === '-') && $onlineSessions->isNotEmpty()) {
                    $mac = trim((string)($onlineSessions->first()->mac_address ?? ($onlineSessions->first()->caller_id ?? '')));
                }
                if (($ip === '' || $ip === '-' || $ip === '0.0.0.0') && $radAccts->isNotEmpty()) {
                    $ip = trim((string)($radAccts->first()->framed_ip_address ?? ''));
                }
                if (($mac === '' || $mac === '-') && $radAccts->isNotEmpty()) {
                    $mac = trim((string)($radAccts->first()->calling_station_id ?? ''));
                }

                // Abaikan jika ghost session: jika IP dan MAC dua-duanya tidak ada, bukan perangkat nyata!
                if (($ip === '' || $ip === '-' || $ip === '0.0.0.0') && ($mac === '' || $mac === '-')) {
                    return;
                }

                // Cek duplikasi
                $exists = collect($devices)->first(function ($d) use ($ip, $mac) {
                    if ($mac !== '' && $mac !== '-' && $d['mac'] === $mac) return true;
                    if ($ip !== '' && $ip !== '-' && $d['ip'] === $ip) return true;
                    return false;
                });

                if (!$exists) {
                    $devIp = ($ip !== '' && $ip !== '0.0.0.0') ? $ip : '-';
                    $devMac = ($mac !== '') ? $mac : '-';
                    $devUptime = ($uptime !== '') ? $uptime : '-';

                    $devices[] = [
                        'id' => $rawId ?? ('dev_' . $index),
                        'ip' => $devIp,
                        'mac' => $devMac,
                        'uptime' => $devUptime,
                        'bytes_in' => (int)$rawBytesIn,
                        'bytes_out' => (int)$rawBytesOut,
                    ];
                    $deviceLines[] = "{$index}. {$devIp} : {$devMac}";
                    $index++;
                }
            };

            // Process Hotspot sessions
            foreach ($activeHsSessions as $s) {
                $resolveDevice($s->address, $s->mac_address, $s->uptime, $s->bytes_in, $s->bytes_out, $s->id);
            }

            // Process PPPoE sessions
            foreach ($activePppSessions as $s) {
                $resolveDevice($s->address, $s->caller_id, $s->uptime, $s->bytes_in, $s->bytes_out, $s->id);
            }

            // Process OnlineSession
            foreach ($onlineSessions as $os) {
                $resolveDevice($os->address, $os->mac_address ?: $os->caller_id, $os->uptime, $os->bytes_in, $os->bytes_out, $os->id);
            }

            // Process RadiusAccounting
            foreach ($radAccts as $ra) {
                $upSec = (int)($ra->acct_session_time ?? 0);
                $upStr = $upSec > 0 ? gmdate('H:i:s', $upSec) : '-';
                $resolveDevice($ra->framed_ip_address, $ra->calling_station_id, $upStr, $ra->acct_input_octets, $ra->acct_output_octets, $ra->id);
            }

            $isOnline = count($devices) > 0;
            $statusText = $isOnline ? 'ONLINE' : 'OFFLINE';

            // Profile Name
            $profileName = 'Hotspot Regular';
            $expiry = '-';
            $download = 0;
            $upload = 0;

            if ($customerService && $customerService->serviceProfile) {
                $profileName = $customerService->serviceProfile->name;
                $expiry = $customerService->expired_at ? $customerService->expired_at->format('Y-m-d H:i:s') : '-';
            } elseif ($pppoeUser && $pppoeUser->serviceProfile) {
                $profileName = $pppoeUser->serviceProfile->name;
                $expiry = $pppoeUser->expires_at ? $pppoeUser->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($hotspotUser && $hotspotUser->serviceProfile) {
                $profileName = $hotspotUser->serviceProfile->name;
                $expiry = $hotspotUser->expires_at ? $hotspotUser->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($voucher && $voucher->serviceProfile) {
                $profileName = $voucher->serviceProfile->name;
                $expiry = $voucher->expires_at ? $voucher->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($customerService) {
                $profileName = $customerService->serviceProfile->name ?? 'Langganan Internet';
                $expiry = $customerService->expired_at ? $customerService->expired_at->format('Y-m-d H:i:s') : '-';
            } elseif ($pppoeUser) {
                $profileName = $pppoeUser->serviceProfile->name ?? 'Langganan PPPoE';
                $expiry = $pppoeUser->expires_at ? $pppoeUser->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($hotspotUser) {
                $profileName = $hotspotUser->serviceProfile->name ?? 'Hotspot Member';
                $expiry = $hotspotUser->expires_at ? $hotspotUser->expires_at->format('Y-m-d H:i:s') : '-';
            }

            // Hitung bytes
            foreach ($devices as $dev) {
                $download += $dev['bytes_out']; // Router out to user = download
                $upload += $dev['bytes_in'];     // User in to router = upload
            }

            // Cari tagihan terakhir jika ada
            $latestInvoice = null;
            $targetCustId = $customer?->id ?? $customerService?->customer_id ?? $pppoeUser?->customerService?->customer_id ?? $hotspotUser?->customerService?->customer_id ?? null;
            if ($targetCustId) {
                $latestInvoice = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->whereNull('deleted_at')->where('customer_id', $targetCustId)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $msg = count($deviceLines) > 0 
                ? implode("\n", $deviceLines) 
                : 'Tidak ada perangkat aktif saat ini.';

            return $this->jsonResponse([
                'code' => 200,
                'status' => 'success',
                'msg' => $msg,
                'data' => [
                    'username' => $unique,
                    'profile_name' => $profileName,
                    'voucher_status' => $statusText,
                    'download' => $download,
                    'upload' => $upload,
                    'last_paid' => $expiry,
                    'invoice_number' => $latestInvoice->invoice_number ?? null,
                    'devices' => $devices,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Hotspot check-user error: ' . $e->getMessage());
            return $this->jsonResponse([
                'code' => 500,
                'status' => 'error',
                'msg' => 'Gagal mengecek user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. GET /api/v1/hotspot/check-bill
     * Check unpaid invoices by username, customer code, phone, or invoice number
     */
    public function checkBill(Request $request): JsonResponse
    {
        $query = trim((string)($request->input('q') ?? $request->input('unique') ?? ''));

        if ($query === '') {
            return $this->jsonResponse([
                'code' => 400,
                'status' => 'error',
                'msg' => 'Nomor Pelanggan, Username, atau No Invoice wajib diisi.',
            ], 400);
        }

        try {
            $queryLower = strtolower($query);
            $user = null;
            $customer = null;

            // 1. Cari User langsung (customer_code, username, pppoe_username, email)
            $user = User::withoutGlobalScopes()
                ->where(function ($q) use ($queryLower) {
                    $q->whereRaw('LOWER(customer_code) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(username) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(pppoe_username) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(email) = ?', [$queryLower]);
                })
                ->first();

            if ($user) {
                $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
            }

            // 2. Cari Customer langsung (Code, Email, Phone, Name)
            if (!$customer) {
                $customer = Customer::withoutGlobalScopes()
                    ->where(function ($q) use ($queryLower) {
                        $q->whereRaw('LOWER(code) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(email) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(phone) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%']);
                    })
                    ->first();
            }

            // 3. Jika input berupa angka (misal format tanggal+urutan '2026101607' atau ID '7')
            if (is_numeric($query)) {
                $num = (int)$query;

                if (!$customer) {
                    $customer = Customer::withoutGlobalScopes()->find($num);
                }
                if (!$user && !$customer) {
                    $user = User::withoutGlobalScopes()->find($num);
                    if ($user) {
                        $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
                    }
                }

                // Format YmdXX (panjang >= 9 digit, misal 2026101607 -> suffix 07 = 7)
                if (strlen($query) >= 9) {
                    $idSuffix = (int) substr($query, 8);
                    if ($idSuffix > 0) {
                        if (!$customer) {
                            $customer = Customer::withoutGlobalScopes()->find($idSuffix);
                        }
                        if (!$customer) {
                            $customer = Customer::withoutGlobalScopes()
                                ->where('code', (string)$idSuffix)
                                ->orWhere('code', str_pad((string)$idSuffix, 2, '0', STR_PAD_LEFT))
                                ->orWhere('code', str_pad((string)$idSuffix, 3, '0', STR_PAD_LEFT))
                                ->orWhere('code', str_pad((string)$idSuffix, 4, '0', STR_PAD_LEFT))
                                ->orWhere('code', 'like', '%' . $idSuffix)
                                ->first();
                        }
                        if (!$user && !$customer) {
                            $user = User::withoutGlobalScopes()->find($idSuffix);
                            if ($user) {
                                $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
                            }
                        }
                    }
                }
            }

            // 4. Jika belum ketemu, cari via PPPoEUser, CustomerService, atau HotspotUser
            if (!$customer) {
                $pppoe = PPPoEUser::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$queryLower])->with(['customerService.customer'])->first();
                if ($pppoe) {
                    $customer = $pppoe->customerService?->customer ?? $pppoe->customer;
                }
            }

            if (!$customer) {
                $cs = CustomerService::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$queryLower])->with('customer')->first();
                if ($cs && $cs->customer) {
                    $customer = $cs->customer;
                }
            }

            if (!$customer) {
                $hu = HotspotUser::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$queryLower])->with(['customerService.customer'])->first();
                if ($hu) {
                    $customer = $hu->customerService?->customer ?? $hu->customer;
                }
            }

            if ($customer && !$user && $customer->user_id) {
                $user = User::withoutGlobalScopes()->find($customer->user_id);
            }

            // 5. Cari Invoice langsung jika input adalah No Invoice
            $directInvoice = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                ->with(['customer', 'items'])
                ->first();

            $invoices = collect();

            if ($directInvoice) {
                $invoices = collect([$directInvoice]);
                if (!$customer) {
                    $customer = $directInvoice->customer;
                }
            } elseif ($customer) {
                $invoices = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                    ->whereNull('deleted_at')
                    ->where('customer_id', $customer->id)
                    ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->with('items')
                    ->get();
            } elseif ($user) {
                $userCustomer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
                if ($userCustomer) {
                    $customer = $userCustomer;
                    $invoices = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                        ->whereNull('deleted_at')
                        ->where('customer_id', $customer->id)
                        ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                        ->orderBy('due_date', 'asc')
                        ->with('items')
                        ->get();
                }
            }

            if ($invoices->isEmpty()) {
                if ($customer || $user) {
                    $custName = $customer?->name ?? $user?->name ?? 'Pelanggan';
                    $custCode = $customer?->code ?? $user?->customer_code ?? $query;
                    return $this->jsonResponse([
                        'code' => 200,
                        'status' => 'paid',
                        'msg' => 'Pelanggan "' . $custName . '" (' . $custCode . ') tidak memiliki tagihan aktif yang belum lunas (semua tagihan lunas).',
                        'data' => [
                            'customer_name' => $custName,
                            'customer_code' => $custCode,
                            'invoices' => [],
                            'total_unpaid' => 0,
                        ],
                    ]);
                }

                return $this->jsonResponse([
                    'code' => 404,
                    'status' => 'not_found',
                    'msg' => 'Tidak ditemukan tagihan atau data pelanggan untuk "' . $query . '". Pastikan ID Pelanggan / No HP / Invoice benar.',
                ], 404);
            }

            $invoiceList = $invoices->map(function ($inv) {
                $remaining = (float)($inv->total_amount - ($inv->paid_amount ?? 0));
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'issue_date' => $inv->issue_date ? $inv->issue_date->format('d/m/Y') : '-',
                    'due_date' => $inv->due_date ? $inv->due_date->format('d/m/Y') : '-',
                    'total_amount' => (float)$inv->total_amount,
                    'paid_amount' => (float)($inv->paid_amount ?? 0),
                    'remaining_amount' => $remaining,
                    'status' => $inv->status,
                    'payment_url' => url('/payment?q=' . urlencode($inv->invoice_number) . '&auto=1'),
                ];
            });

            $totalUnpaid = $invoices->sum(fn($i) => $i->total_amount - ($i->paid_amount ?? 0));

            return $this->jsonResponse([
                'code' => 200,
                'status' => 'success',
                'customer' => [
                    'id' => $customer->id ?? null,
                    'name' => $customer->name ?? '-',
                    'code' => $customer->code ?? '-',
                    'phone' => $customer->phone ?? '-',
                ],
                'invoices' => $invoiceList,
                'total_unpaid' => (float)$totalUnpaid,
            ]);
        } catch (Throwable $e) {
            Log::error('Hotspot check-bill error: ' . $e->getMessage());
            return $this->jsonResponse([
                'code' => 500,
                'status' => 'error',
                'msg' => 'Gagal mengecek tagihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 4. POST|GET /api/v1/hotspot/kick-user
     * Disconnect active hotspot sessions for a user or specific MAC address
     */
    public function kickUser(Request $request, SessionKickService $kickService, RouterOSService $routerOS): JsonResponse
    {
        $username = trim((string)($request->input('username') ?? $request->input('unique') ?? ''));
        $mac = trim((string)($request->input('mac') ?? $request->input('mac_address') ?? ''));

        if ($username === '' && $mac === '') {
            return $this->jsonResponse([
                'code' => 400,
                'status' => 'error',
                'msg' => 'Username atau MAC address wajib diisi untuk melakukan disconnect/reset.',
            ], 400);
        }

        try {
            $kickedCount = 0;

            // 1. Kick dari database HotspotActiveSession
            $query = HotspotActiveSession::with('router');
            if ($username !== '') {
                $query->where('user', $username);
            }
            if ($mac !== '') {
                $query->where('mac_address', $mac);
            }
            $activeSessions = $query->get();

            foreach ($activeSessions as $session) {
                try {
                    $result = $kickService->kickHotspot($session, null);
                    if ($result['success'] ?? false) {
                        $kickedCount++;
                    }
                } catch (Throwable $e) {
                    Log::warning('SessionKickService exception: ' . $e->getMessage());
                }
            }

            // 2. Disconnect langsung ke semua Router via RouterOS API jika user belum terputus atau sesi tidak di DB
            if ($username !== '') {
                $routers = Router::where('is_active', true)->get();
                foreach ($routers as $router) {
                    try {
                        $driver = $routerOS->getDriver($router);
                        if (method_exists($driver, 'disconnectHotspotUser')) {
                            $res = $driver->disconnectHotspotUser($username);
                            if ($res) {
                                $kickedCount++;
                            }
                        }
                    } catch (Throwable $e) {
                        Log::info('Direct router kick skipped for router ' . $router->name . ': ' . $e->getMessage());
                    }
                }
            }

            // 3. Bersihkan sesi di database jika tersisa
            if ($username !== '') {
                HotspotActiveSession::where('user', $username)->delete();
                PppActiveSession::where('name', $username)->delete();
                OnlineSession::where('username', $username)->delete();
                RadiusAccounting::where('username', $username)->whereNull('acct_stop_time')->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => 'User-Kick-Web',
                ]);
            }
            if ($mac !== '') {
                HotspotActiveSession::where('mac_address', $mac)->delete();
                PppActiveSession::where('caller_id', $mac)->delete();
                OnlineSession::where('mac_address', $mac)->orWhere('caller_id', $mac)->delete();
                RadiusAccounting::where('calling_station_id', $mac)->whereNull('acct_stop_time')->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => 'User-Kick-Web',
                ]);
            }

            return $this->jsonResponse([
                'code' => 200,
                'status' => 'success',
                'msg' => 'Reset berhasil! Perangkat telah diputuskan. Silahkan coba login kembali.',
                'kicked_sessions' => $kickedCount,
            ]);
        } catch (Throwable $e) {
            Log::error('Hotspot kick-user error: ' . $e->getMessage());
            return $this->jsonResponse([
                'code' => 500,
                'status' => 'error',
                'msg' => 'Terjadi kesalahan saat memutus koneksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 5. POST|OPTIONS /api/v1/hotspot/register-lead
     * Menerima form pendaftaran baru (Hotspot Member / PPPoE) dari LoginPage atau landing
     */
    public function registerLead(Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->jsonResponse(['code' => 200, 'msg' => 'OK']);
        }

        try {
            $name = trim((string)$request->input('name', ''));
            $phone = trim((string)$request->input('phone', ''));
            $address = trim((string)$request->input('address', ''));
            $packageName = trim((string)$request->input('package', ''));
            $packageId = $request->input('package_id');

            if (empty($name)) {
                return $this->jsonResponse(['code' => 422, 'status' => 'error', 'msg' => 'Nama lengkap wajib diisi.'], 422);
            }
            if (empty($phone)) {
                return $this->jsonResponse(['code' => 422, 'status' => 'error', 'msg' => 'Nomor WhatsApp wajib diisi.'], 422);
            }
            if (empty($address)) {
                return $this->jsonResponse(['code' => 422, 'status' => 'error', 'msg' => 'Alamat pemasangan / lokasi wajib diisi.'], 422);
            }

            if (empty($packageName) && !empty($packageId)) {
                $profile = ServiceProfile::withoutGlobalScopes()->find($packageId);
                if ($profile) {
                    $packageName = $profile->name;
                }
            }

            $lead = \App\Models\CRM\Lead::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => 'Pendaftaran paket: ' . ($packageName ?: '-'),
                'source' => 'hotspot_loginpage',
                'status' => 'new',
            ]);

            // Kirim notifikasi WhatsApp ke Admin jika dikonfigurasi
            try {
                $adminPhone = \App\Models\User::whereHas('roles', function($q) {
                    $q->where('name', 'admin');
                })->whereNotNull('whatsapp')->first()?->whatsapp 
                ?? \App\Models\Setting::getValue('company.phone');

                if ($adminPhone) {
                    $waService = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class);
                    $messageText = "*[PENDAFTARAN BARU - LOGINPAGE HOTSPOT]*\n\n"
                        . "Ada pendaftaran baru dari LoginPage Hotspot:\n"
                        . "Nama: {$lead->name}\n"
                        . "WA: {$lead->phone}\n"
                        . "Paket: " . ($packageName ?: '-') . "\n"
                        . "Alamat: {$lead->address}\n\n"
                        . "Silakan tindak lanjuti di panel Admin menu Calon Pelanggan.";

                    $msg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                        toPhone: $adminPhone,
                        type: 'text',
                        text: $messageText,
                        category: 'info',
                        priorityHigh: true
                    );
                    $waService->sendImmediate($msg);
                }
            } catch (\Throwable $th) {
                Log::warning('Hotspot register-lead WA notif error: ' . $th->getMessage());
            }

            return $this->jsonResponse([
                'code' => 200,
                'status' => 'success',
                'msg' => 'Terima kasih, pendaftaran Anda telah berhasil dikirim! Tim kami akan segera menghubungi Anda.',
                'lead_id' => $lead->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Hotspot register-lead error: ' . $e->getMessage());
            return $this->jsonResponse([
                'code' => 500,
                'status' => 'error',
                'msg' => 'Gagal memproses pendaftaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
