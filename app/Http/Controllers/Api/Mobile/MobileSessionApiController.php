<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PppActiveSession;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobileSessionApiController extends Controller
{
    /**
     * List active user sessions (PPPoE, Hotspot, Voucher) scoped for reseller / staff.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        $tab = strtolower(trim((string) ($request->input('tab') ?? $request->input('type') ?? 'all')));
        $search = trim((string) ($request->input('q') ?? $request->input('search') ?? ''));

        // Base PPPoE Query
        $pppoeQuery = PppActiveSession::with([
            'router',
            'pppoeUser' => fn($q) => $q->withoutGlobalScopes()->with([
                'reseller',
                'customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
            ]),
        ]);

        // Base Voucher Query (Hotspot session matching voucher code)
        $voucherQuery = HotspotActiveSession::with(['router', 'voucher.reseller'])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
            });

        // Base Hotspot Member Query (Hotspot session excluding vouchers)
        $hotspotQuery = HotspotActiveSession::with([
            'router',
            'hotspotUser' => fn($q) => $q->withoutGlobalScopes()->with([
                'reseller',
                'customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
            ]),
        ])->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
        });

        // Reseller Tenant Scoping (strictly matching UserOnline.php)
        if ($ownerId) {
            $pppoeQuery->whereExists(function ($sub) use ($ownerId) {
                $sub->select(DB::raw(1))
                    ->from('pppoe_users')
                    ->leftJoin('customer_services', 'pppoe_users.customer_service_id', '=', 'customer_services.id')
                    ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                    ->where(function ($uq) {
                        $uq->whereRaw('LOWER(pppoe_users.username) = LOWER(ppp_active_sessions.name)')
                           ->orWhereRaw('LOWER(ppp_active_sessions.name) LIKE LOWER(pppoe_users.username) || \'@%\'')
                           ->orWhereRaw('LOWER(pppoe_users.username) LIKE LOWER(ppp_active_sessions.name) || \'@%\'');
                    })
                    ->whereNull('pppoe_users.deleted_at')
                    ->where(function ($q) use ($ownerId) {
                        $q->where('pppoe_users.reseller_id', $ownerId)
                          ->orWhere('customers.reseller_id', $ownerId)
                          ->orWhere('customers.created_by', $ownerId)
                          ->orWhere('customer_services.reseller_id', $ownerId);
                    });
            });

            $hotspotQuery->whereExists(function ($sub) use ($ownerId) {
                $sub->select(DB::raw(1))
                    ->from('hotspot_users')
                    ->leftJoin('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
                    ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                    ->where(function ($uq) {
                        $uq->whereRaw('LOWER(hotspot_users.username) = LOWER(hotspot_active_sessions.user)')
                           ->orWhereRaw('LOWER(hotspot_active_sessions.user) LIKE LOWER(hotspot_users.username) || \'@%\'')
                           ->orWhereRaw('LOWER(hotspot_users.username) LIKE LOWER(hotspot_active_sessions.user) || \'@%\'');
                    })
                    ->whereNull('hotspot_users.deleted_at')
                    ->where(function ($q) use ($ownerId) {
                        $q->where('hotspot_users.reseller_id', $ownerId)
                          ->orWhere('customers.reseller_id', $ownerId)
                          ->orWhere('customers.created_by', $ownerId)
                          ->orWhere('customer_services.reseller_id', $ownerId);
                    });
            });

            $voucherQuery->whereExists(function ($sub) use ($ownerId) {
                $sub->select(DB::raw(1))
                    ->from('vouchers')
                    ->whereRaw('vouchers.code = hotspot_active_sessions.user')
                    ->where('vouchers.reseller_id', $ownerId);
            });
        }

        // Global stats calculation
        $stats = [
            'pppoe' => (clone $pppoeQuery)->count(),
            'hotspot' => (clone $hotspotQuery)->count(),
            'voucher' => (clone $voucherQuery)->count(),
        ];
        $stats['total'] = $stats['pppoe'] + $stats['hotspot'] + $stats['voucher'];

        // Search filtering
        if ($search !== '') {
            $pppoeQuery->where(function ($sub) use ($search) {
                $sub->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('caller_id', 'LIKE', "%{$search}%")
                    ->orWhereHas('router', fn($rq) => $rq->where('name', 'LIKE', "%{$search}%"));
            });

            $hotspotQuery->where(function ($sub) use ($search) {
                $sub->where('user', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('mac_address', 'LIKE', "%{$search}%")
                    ->orWhereHas('router', fn($rq) => $rq->where('name', 'LIKE', "%{$search}%"));
            });

            $voucherQuery->where(function ($sub) use ($search) {
                $sub->where('user', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('mac_address', 'LIKE', "%{$search}%")
                    ->orWhereHas('router', fn($rq) => $rq->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $sessionItems = collect();

        // 1. PPPoE Sessions
        if ($tab === 'all' || $tab === 'pppoe') {
            $pppoeList = $pppoeQuery->orderBy('session_started_at', 'desc')->limit(100)->get();
            foreach ($pppoeList as $s) {
                $customer = $s->pppoeUser?->customer;
                $profile = $s->pppoeUser?->customerService?->serviceProfile?->name 
                    ?? $s->pppoeUser?->serviceProfile?->name 
                    ?? 'PPPoE Home';

                $sessionItems->push([
                    'id' => $s->id,
                    'type' => 'pppoe',
                    'username' => $s->name,
                    'ip_address' => $s->address ?? '-',
                    'mac_address' => $s->caller_id ?? '-',
                    'uptime' => $s->uptime ?? '0s',
                    'bytes_in' => (int) ($s->bytes_in ?? 0),
                    'bytes_out' => (int) ($s->bytes_out ?? 0),
                    'router_id' => $s->router_id,
                    'router_name' => $s->router?->name ?? 'Router PPPoE',
                    'customer_name' => $customer?->name ?? $s->name,
                    'customer_code' => $customer?->code ?? null,
                    'service_profile' => $profile,
                    'session_started_at' => $s->session_started_at?->toIso8601String(),
                ]);
            }
        }

        // 2. Hotspot Sessions
        if ($tab === 'all' || $tab === 'hotspot') {
            $hotspotList = $hotspotQuery->orderBy('session_started_at', 'desc')->limit(100)->get();
            foreach ($hotspotList as $s) {
                $customer = $s->hotspotUser?->customer;
                $profile = $s->hotspotUser?->customerService?->serviceProfile?->name
                    ?? $s->hotspotUser?->serviceProfile?->name
                    ?? 'Hotspot Member';

                $sessionItems->push([
                    'id' => $s->id,
                    'type' => 'hotspot',
                    'username' => $s->user,
                    'ip_address' => $s->address ?? '-',
                    'mac_address' => $s->mac_address ?? '-',
                    'uptime' => $s->uptime ?? '0s',
                    'bytes_in' => (int) ($s->bytes_in ?? 0),
                    'bytes_out' => (int) ($s->bytes_out ?? 0),
                    'router_id' => $s->router_id,
                    'router_name' => $s->router?->name ?? 'Router Hotspot',
                    'customer_name' => $customer?->name ?? $s->user,
                    'customer_code' => $customer?->code ?? null,
                    'service_profile' => $profile,
                    'session_started_at' => $s->session_started_at?->toIso8601String(),
                ]);
            }
        }

        // 3. Voucher Sessions
        if ($tab === 'all' || $tab === 'voucher') {
            $voucherList = $voucherQuery->orderBy('session_started_at', 'desc')->limit(100)->get();
            foreach ($voucherList as $s) {
                $voucher = $s->voucher;
                $profile = 'Voucher ' . ($voucher?->profile ?? 'Fisik');

                $sessionItems->push([
                    'id' => $s->id,
                    'type' => 'voucher',
                    'username' => $s->user,
                    'ip_address' => $s->address ?? '-',
                    'mac_address' => $s->mac_address ?? '-',
                    'uptime' => $s->uptime ?? '0s',
                    'bytes_in' => (int) ($s->bytes_in ?? 0),
                    'bytes_out' => (int) ($s->bytes_out ?? 0),
                    'router_id' => $s->router_id,
                    'router_name' => $s->router?->name ?? 'Router Hotspot',
                    'customer_name' => 'Voucher: ' . $s->user,
                    'customer_code' => $voucher?->code ?? $s->user,
                    'service_profile' => $profile,
                    'session_started_at' => $s->session_started_at?->toIso8601String(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'tab' => $tab,
            'stats' => $stats,
            'data' => $sessionItems->values(),
        ]);
    }

    /**
     * Terminate / Kick active user session safely via RFC5176 PoD and MikroTik API.
     */
    public function kick(Request $request, string $type, int $id): JsonResponse
    {
        $user = $request->user();
        $kicker = app(SessionKickService::class);
        $type = strtolower($type);

        try {
            if ($type === 'pppoe') {
                $session = PppActiveSession::with('router')->findOrFail($id);
                $result = $kicker->kickPppoe($session, $user);
            } else {
                $session = HotspotActiveSession::with('router')->findOrFail($id);
                $result = $kicker->kickHotspot($session, $user);
            }

            $success = $result['success'] ?? false;
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Sesi berhasil diputuskan!' : 'Permintaan putus sesi dikirim ke router.',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Mobile session kick failed', [
                'type' => $type,
                'session_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memutuskan sesi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
