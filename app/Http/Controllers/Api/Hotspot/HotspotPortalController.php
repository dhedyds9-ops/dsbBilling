<?php

namespace App\Http\Controllers\Api\Hotspot;

use App\Http\Controllers\Controller;
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
            $profiles = ServiceProfile::query()
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
            $hotspotUser = HotspotUser::where('username', $unique)->with('serviceProfile')->first();
            $voucher = null;
            $customerService = null;
            $pppoeUser = null;

            if (!$hotspotUser) {
                $voucher = Voucher::where('code', $unique)->with('serviceProfile')->first();
            }

            if (!$hotspotUser && !$voucher) {
                $pppoeUser = PPPoEUser::where('username', $unique)->with(['customerService.customer', 'serviceProfile'])->first();
            }

            if (!$hotspotUser && !$voucher && !$pppoeUser) {
                $customerService = CustomerService::where('username', $unique)->with(['customer', 'serviceProfile'])->first();
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
                $latestInvoice = Invoice::where('customer_id', $targetCustId)
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

            // 1. Cari Customer langsung
            $customer = Customer::query()
                ->whereRaw('LOWER(code) = ?', [$queryLower])
                ->orWhere('id', is_numeric($query) ? (int)$query : 0)
                ->orWhereRaw('LOWER(email) = ?', [$queryLower])
                ->orWhereRaw('LOWER(phone) = ?', [$queryLower])
                ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%'])
                ->first();

            // 2. Jika belum ketemu, cari via PPPoEUser, CustomerService, atau HotspotUser
            if (!$customer) {
                $pppoe = PPPoEUser::whereRaw('LOWER(username) = ?', [$queryLower])->with(['customerService.customer'])->first();
                if ($pppoe) {
                    $customer = $pppoe->customerService?->customer ?? $pppoe->customer;
                }
            }

            if (!$customer) {
                $cs = CustomerService::whereRaw('LOWER(username) = ?', [$queryLower])->with('customer')->first();
                if ($cs && $cs->customer) {
                    $customer = $cs->customer;
                }
            }

            if (!$customer) {
                $hu = HotspotUser::whereRaw('LOWER(username) = ?', [$queryLower])->with(['customerService.customer'])->first();
                if ($hu) {
                    $customer = $hu->customerService?->customer ?? $hu->customer;
                }
            }

            // 3. Cari Invoice langsung jika input adalah No Invoice
            $directInvoice = Invoice::query()
                ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                ->with(['customer', 'items'])
                ->first();

            $invoices = collect();

            if ($customer) {
                $invoices = Invoice::query()
                    ->where('customer_id', $customer->id)
                    ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->with('items')
                    ->get();
            } elseif ($directInvoice) {
                $invoices = collect([$directInvoice]);
                $customer = $directInvoice->customer;
            }

            if ($invoices->isEmpty()) {
                return $this->jsonResponse([
                    'code' => 404,
                    'status' => 'not_found',
                    'msg' => 'Tidak ditemukan tagihan belum lunas untuk "' . $query . '". Pastikan ID Pelanggan / No HP / Invoice benar.',
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
