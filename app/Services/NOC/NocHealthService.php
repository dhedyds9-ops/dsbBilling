<?php

namespace App\Services\NOC;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Router;
use App\Models\ISP\OnlineSession;
use App\Models\Alarm;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NocHealthService
{
    const CACHE_TTL = 60;

    public function getNetworkHealthSummary(): array
    {
        return Cache::remember('noc:health_summary', self::CACHE_TTL, function () {
            $oltThreshold = now()->subMinutes(15)->toDateTimeString();
            $onuThreshold = now()->subMinutes(5)->toDateTimeString();

            $oltTotal = Olt::count();
            $oltOnline = Olt::where('status', 'active')
                ->where('last_polled_at', '>=', $oltThreshold)
                ->count();
            $oltOffline = Olt::where('status', '!=', 'active')
                ->orWhereNull('last_polled_at')
                ->orWhere('last_polled_at', '<', $oltThreshold)
                ->count();
            $oltWarning = 0;

            $onuTotal = Onu::count();
            $onuOnline = Onu::where('last_seen_at', '>=', $onuThreshold)->count();
            $onuOffline = $onuTotal - $onuOnline;
            $onuLos = Onu::where('status', 'los')
                ->orWhere(function ($q) {
                    $q->where('rx_power_dbm', '<', -30)->where('status', '!=', 'offline');
                })->count();
            $onuLowRx = Onu::whereBetween('rx_power_dbm', [-30, -27])->count();

            $ponTotal = PonPort::count();

            $ponHealth = DB::table('pon_ports')
                ->leftJoin('onus', 'pon_ports.id', '=', 'onus.pon_port_id')
                ->select(
                    'pon_ports.id',
                    DB::raw('COUNT(onus.id) as total_onus'),
                    DB::raw("SUM(CASE WHEN onus.last_seen_at >= '{$onuThreshold}' THEN 1 ELSE 0 END) as online_onus")
                )
                ->groupBy('pon_ports.id')
                ->get();

            $ponHealthy = 0;
            $ponWarning = 0;
            $ponDown = 0;

            foreach ($ponHealth as $p) {
                if ($p->total_onus == 0) continue;
                if ($p->online_onus == $p->total_onus) $ponHealthy++;
                elseif ($p->online_onus == 0) $ponDown++;
                else $ponWarning++;
            }

            $routerTotal = Router::count();
            $routerOnline = Router::whereHas('monitoringLogs', function ($q) {
                $q->where('is_online', true)
                  ->where('created_at', '>=', now()->subMinutes(5));
            })->count();
            $routerOffline = $routerTotal - $routerOnline;

            $pppoe = OnlineSession::pppoe()->count();
            $hotspot = OnlineSession::hotspot()->count();
            $voucher = 0;

            $criticalAlarms = Alarm::where('status', 'open')->where('level', 'critical')->count();
            $warningAlarms = Alarm::where('status', 'open')->where('level', 'warning')->count();
            $openAlarms = Alarm::where('status', 'open')->count();

            return [
                'olt' => ['total' => $oltTotal, 'online' => $oltOnline, 'offline' => $oltOffline, 'warning' => $oltWarning],
                'onu' => ['total' => $onuTotal, 'online' => $onuOnline, 'offline' => $onuOffline, 'los' => $onuLos, 'low_rx' => $onuLowRx],
                'pon' => ['total' => $ponTotal, 'healthy' => $ponHealthy, 'warning' => $ponWarning, 'down' => $ponDown],
                'router' => ['total' => $routerTotal, 'online' => $routerOnline, 'offline' => $routerOffline],
                'services' => ['pppoe' => $pppoe, 'hotspot' => $hotspot, 'voucher' => $voucher],
                'alarms' => ['critical' => $criticalAlarms, 'warning' => $warningAlarms, 'open' => $openAlarms]
            ];
        });
    }

    public function getHeaderStats(): array
    {
        return Cache::remember('noc:header_stats', self::CACHE_TTL, function () {
            $oltTotal = Olt::count();
            $oltOnline = Olt::where('status', 'active')
                ->where('last_polled_at', '>=', now()->subMinutes(15))
                ->count();

            $onuTotal = Onu::count();
            $onuOnline = Onu::where('last_seen_at', '>=', now()->subMinutes(5))->count();

            $routerTotal = Router::count();
            $routerOnline = Router::whereHas('monitoringLogs', function ($q) {
                $q->where('is_online', true)
                  ->where('created_at', '>=', now()->subMinutes(5));
            })->count();

            $pppoe = OnlineSession::pppoe()->count();

            return [
                'olt' => ['online' => $oltOnline, 'total' => $oltTotal],
                'onu' => ['online' => $onuOnline, 'total' => $onuTotal],
                'router' => ['online' => $routerOnline, 'total' => $routerTotal],
                'pppoe_active' => $pppoe
            ];
        });
    }

    public function getDeviceTable(string $filter = 'all', string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        $oltThreshold = now()->subMinutes(15)->toDateTimeString();
        $onuThreshold = now()->subMinutes(5)->toDateTimeString();
        $routerThreshold = now()->subMinutes(5)->toDateTimeString();
        $queries = [];

        if (in_array($filter, ['all', 'olt'])) {
            $queries[] = DB::table('olts')
                ->select(
                    DB::raw(\App\Services\Support\DbCompat::concatId('olt_') . ' as id'),
                    DB::raw("COALESCE(name, code) as name"),
                    DB::raw("'OLT' as type"),
                    DB::raw("ip_address as ip"),
                    DB::raw("CASE WHEN status = 'active' AND last_polled_at >= '{$oltThreshold}' THEN 'Online' ELSE 'Offline' END as status"),
                    DB::raw("last_polled_at as last_seen")
                )
                ->whereNull('deleted_at')
                ->when($search, function($q) use ($search) {
                    $q->where(function($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%");
                    });
                });
        }

        if (in_array($filter, ['all', 'onu'])) {
            $queries[] = DB::table('onus')
                ->select(
                    DB::raw(\App\Services\Support\DbCompat::concatId('onu_') . ' as id'),
                    DB::raw("COALESCE(name, COALESCE(serial_number, mac_address)) as name"),
                    DB::raw("'ONU' as type"),
                    DB::raw("'-' as ip"),
                    DB::raw("CASE WHEN last_seen_at >= '{$onuThreshold}' THEN 'Online' ELSE 'Offline' END as status"),
                    DB::raw("last_seen_at as last_seen")
                )
                ->whereNull('deleted_at')
                ->when($search, function($q) use ($search) {
                    $q->where(function($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('mac_address', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
                });
        }

        if (in_array($filter, ['all', 'router'])) {
            $queries[] = DB::table('routers')
                ->select(
                    DB::raw(\App\Services\Support\DbCompat::concatId('router_') . ' as id'),
                    DB::raw("COALESCE(name, code) as name"),
                    DB::raw("'Router' as type"),
                    DB::raw("ip_address as ip"),
                    DB::raw("CASE WHEN status != 'disabled' AND last_seen_at >= '{$routerThreshold}' THEN 'Online' ELSE 'Offline' END as status"),
                    DB::raw("last_seen_at as last_seen")
                )
                ->whereNull('deleted_at')
                ->when($search, function($q) use ($search) {
                    $q->where(function($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%");
                    });
                });
        }

        if (in_array($filter, ['all', 'pppoe'])) {
            $queries[] = DB::table('online_sessions')
                ->select(
                    DB::raw(\App\Services\Support\DbCompat::concatId('session_') . ' as id'),
                    DB::raw("username as name"),
                    DB::raw("'PPPoE' as type"),
                    DB::raw("address as ip"),
                    DB::raw("'Online' as status"),
                    DB::raw("last_seen_at as last_seen")
                )
                ->where('protocol', 'pppoe')
                ->when($search, function($q) use ($search) {
                    $q->where(function($sub) use ($search) {
                        $sub->where('username', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
                });
        }

        if (empty($queries)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $baseQuery = array_shift($queries);
        foreach ($queries as $q) {
            $baseQuery->unionAll($q);
        }

        // To paginate a union query properly, wrap it in a derived table
        $wrappedQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as combined"))
            ->mergeBindings($baseQuery);

        return $wrappedQuery->paginate($perPage);
    }
}
