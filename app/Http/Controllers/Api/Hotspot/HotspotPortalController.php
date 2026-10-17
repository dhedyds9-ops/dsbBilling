<?php

namespace App\Http\Controllers\Api\Hotspot;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\OnlineSession;
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
     * Returns list of hotspot packages / vouchers from ServiceProfile
     */
    public function packages(): JsonResponse
    {
        try {
            $profiles = ServiceProfile::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->whereIn('service_type', ['Hotspot', 'Voucher'])
                      ->orWhere('service_type', 'like', '%Hotspot%')
                      ->orWhere('service_type', 'like', '%Voucher%');
                })
                ->orderBy('base_price', 'asc')
                ->get();

            $packages = $profiles->map(function ($profile) {
                // Formatting time & active period
                $timeLimit = '-';
                if (!empty($profile->duration_value)) {
                    $timeLimit = $profile->duration_value . ' ' . ($profile->duration_unit ?? 'Jam');
                } elseif (!empty($profile->validity_hours)) {
                    $timeLimit = $profile->validity_hours . ' Jam';
                } elseif (!empty($profile->validity_days)) {
                    $timeLimit = $profile->validity_days . ' Hari';
                }

                $activePeriod = '-';
                if (!empty($profile->validity_days)) {
                    $activePeriod = $profile->validity_days . ' Hari';
                } elseif (!empty($profile->validity_hours)) {
                    $activePeriod = $profile->validity_hours . ' Jam';
                } else {
                    $activePeriod = $timeLimit;
                }

                // Quota
                $quotaLimit = 'Unlimited';
                if (!empty($profile->quota_value) && $profile->quota_value > 0) {
                    $quotaLimit = $profile->quota_value . ' ' . ($profile->quota_unit ?? 'MB');
                }

                // Speed
                $speed = '-';
                if (!empty($profile->download_speed) || !empty($profile->upload_speed)) {
                    $speed = ($profile->download_speed ?? '-') . ' / ' . ($profile->upload_speed ?? '-');
                }

                $price = (int) ($profile->promo_price > 0 ? $profile->promo_price : ($profile->base_price ?? $profile->cost_price ?? 0));

                return [
                    'id' => $profile->id,
                    'profile_name' => $profile->name,
                    'service_type' => $profile->service_type,
                    'price_sell' => $price,
                    'shared' => (int) ($profile->max_devices ?? $profile->radius_simultaneous_use ?? 1),
                    'time_limit' => $timeLimit,
                    'active_period' => $activePeriod,
                    'quota_limit' => $quotaLimit,
                    'speed' => $speed,
                    'description' => $profile->description ?? '',
                    'buy_url' => url('/bayar-tagihan?package_id=' . $profile->id),
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

            // 2. Ambil sesi aktif
            $activeSessions = HotspotActiveSession::where('user', $unique)->with('router')->get();
            $onlineSessions = OnlineSession::where('protocol', 'hotspot')->where('username', $unique)->get();

            $devices = [];
            $deviceLines = [];
            $index = 1;

            // Merge active sessions
            foreach ($activeSessions as $s) {
                $ip = $s->address ?? '-';
                $mac = $s->mac_address ?? '-';
                $devices[] = [
                    'id' => $s->id,
                    'ip' => $ip,
                    'mac' => $mac,
                    'uptime' => $s->uptime ?? '-',
                    'bytes_in' => (int)$s->bytes_in,
                    'bytes_out' => (int)$s->bytes_out,
                ];
                $deviceLines[] = "{$index}. {$ip} : {$mac}";
                $index++;
            }

            // Tambahkan dari OnlineSession jika belum ada di list
            foreach ($onlineSessions as $os) {
                $mac = $os->mac_address ?? $os->caller_id ?? '-';
                $already = collect($devices)->firstWhere('mac', $mac);
                if (!$already && $mac !== '-') {
                    $ip = $os->address ?? '-';
                    $devices[] = [
                        'id' => $os->id,
                        'ip' => $ip,
                        'mac' => $mac,
                        'uptime' => $os->uptime ?? '-',
                        'bytes_in' => (int)$os->bytes_in,
                        'bytes_out' => (int)$os->bytes_out,
                    ];
                    $deviceLines[] = "{$index}. {$ip} : {$mac}";
                    $index++;
                }
            }

            $isOnline = count($devices) > 0;
            $statusText = $isOnline ? 'ONLINE' : 'OFFLINE';

            // Profile Name
            $profileName = 'Hotspot Regular';
            $expiry = '-';
            $download = 0;
            $upload = 0;

            if ($hotspotUser) {
                $profileName = $hotspotUser->serviceProfile->name ?? 'Hotspot Member';
                $expiry = $hotspotUser->expires_at ? $hotspotUser->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($voucher) {
                $profileName = $voucher->serviceProfile->name ?? 'Voucher Hotspot';
                $expiry = $voucher->expires_at ? $voucher->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($pppoeUser) {
                $profileName = $pppoeUser->serviceProfile->name ?? 'Langganan PPPoE';
                $expiry = $pppoeUser->expires_at ? $pppoeUser->expires_at->format('Y-m-d H:i:s') : '-';
            } elseif ($customerService) {
                $profileName = $customerService->serviceProfile->name ?? 'Langganan Internet';
                $expiry = $customerService->expired_at ? $customerService->expired_at->format('Y-m-d H:i:s') : '-';
            }

            // Hitung bytes
            foreach ($devices as $dev) {
                $download += $dev['bytes_out']; // Router out to user = download
                $upload += $dev['bytes_in'];     // User in to router = upload
            }

            // Cari tagihan terakhir jika ada
            $latestInvoice = null;
            $targetCustId = $customerService?->customer_id ?? $pppoeUser?->customerService?->customer_id ?? $hotspotUser?->customerService?->customer_id ?? null;
            if ($targetCustId) {
                $latestInvoice = Invoice::withoutGlobalScopes()->where('customer_id', $targetCustId)
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
            $directInvoice = Invoice::withoutGlobalScopes()
                ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                ->with(['customer', 'items'])
                ->first();

            $invoices = collect();

            if ($directInvoice) {
                $invoices = collect([$directInvoice]);
                if (!$customer) {
                    $customer = $directInvoice->customer;
                }
            } elseif ($customer || $user) {
                $targetIds = array_values(array_unique(array_filter([$customer?->id, $user?->id])));
                $invoices = Invoice::withoutGlobalScopes()
                    ->where(function ($q) use ($targetIds, $customer, $user) {
                        if (!empty($targetIds)) {
                            $q->whereIn('customer_id', $targetIds);
                        }
                        if ($customer?->code) {
                            $q->orWhere('customer_id', $customer->code);
                        }
                        if ($user?->customer_code) {
                            $q->orWhere('customer_id', $user->customer_code);
                        }
                    })
                    ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->with('items')
                    ->get();
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
                    'payment_url' => url('/bayar-tagihan?q=' . urlencode($inv->invoice_number)),
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
                OnlineSession::where('protocol', 'hotspot')->where('username', $username)->delete();
            }
            if ($mac !== '') {
                HotspotActiveSession::where('mac_address', $mac)->delete();
                OnlineSession::where('protocol', 'hotspot')->where('mac_address', $mac)->delete();
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
}
