<?php

namespace App\Services\NOC;

use App\Models\ISP\RouterMonitoringLog;
use App\Models\ISP\OltMetric;
use App\Models\ISP\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrafficMonitoringService
{
    const CACHE_TTL = 30; // seconds

    /**
     * Get traffic time-series data.
     *
     * Strategy:
     * - Router traffic: active from RouterMonitoringLog (rate_up / rate_down or bytes_in/bytes_out)
     * - OLT traffic: only if OltMetric rows with tx_bps/rx_bps metric_key actually exist
     * - NEVER fabricate data
     */
    public function getTrafficSeries(
        string $period = '1h',
        string $filter = 'all',
        ?int $deviceId = null,
        string $deviceType = 'router'
    ): array {
        $cacheKey = "noc.traffic.{$period}.{$filter}.{$deviceType}.{$deviceId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period, $filter, $deviceId, $deviceType) {
            $routerData = [];
            $oltData    = [];

            if (in_array($filter, ['all', 'router'])) {
                $routerData = $this->getRouterTraffic($period, $deviceId);
            }

            if (in_array($filter, ['all', 'olt'])) {
                $oltData = $this->getOltTrafficIfAvailable($period, $deviceId);
            }

            // Merge router + OLT time-series (if both available, sum by timestamp bucket)
            if (!empty($routerData) && !empty($oltData)) {
                return $this->mergeTrafficSeries($routerData, $oltData);
            }

            return !empty($routerData) ? $routerData : $oltData;
        });
    }

    /**
     * Get current aggregate traffic totals (for header display).
     * Uses latest RouterMonitoringLog entries.
     */
    public function getTotalCurrentTraffic(): array
    {
        return Cache::remember('noc.traffic.current', self::CACHE_TTL, function () {
            // Sum tx_bps / rx_bps from latest log per router
            $latest = DB::table('router_monitoring_logs as rml')
                ->join(
                    DB::raw('(SELECT router_id, MAX(id) as max_id FROM router_monitoring_logs WHERE is_online = 1 GROUP BY router_id) as latest'),
                    function ($join) {
                        $join->on('rml.router_id', '=', 'latest.router_id')
                             ->on('rml.id', '=', 'latest.max_id');
                    }
                )
                ->selectRaw('
                    SUM(COALESCE(rx_bps, 0)) as total_rx,
                    SUM(COALESCE(tx_bps, 0)) as total_tx,
                    COUNT(*) as router_count
                ')
                ->first();

            $rxBps = (int)($latest->total_rx ?? 0);
            $txBps = (int)($latest->total_tx ?? 0);

            // Fallback to active sessions sum if router logs are not populated yet
            if (!$latest || ($latest->router_count ?? 0) === 0) {
                $sessionStats = DB::table('online_sessions')
                    ->selectRaw('
                        SUM(COALESCE(rate_up, 0)) as total_rx_bps,
                        SUM(COALESCE(rate_down, 0)) as total_tx_bps
                    ')
                    ->first();

                $rxBps = (int)($sessionStats->total_rx_bps ?? 0);
                $txBps = (int)($sessionStats->total_tx_bps ?? 0);
            }

            return [
                'tx_bps'   => $txBps,
                'rx_bps'   => $rxBps,
                'tx_human' => $this->formatBps($txBps),
                'rx_human' => $this->formatBps($rxBps),
                'source'   => 'router',
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function getRouterTraffic(string $period, ?int $routerId = null): array
    {
        $since       = $this->periodToDateTime($period);
        $bucketExpr  = \App\Services\Support\DbCompat::dateBucket('created_at', $period);

        $query = DB::table('router_monitoring_logs')
            ->where('is_online', true)
            ->where('created_at', '>=', $since)
            ->selectRaw("
                {$bucketExpr} as time_bucket,
                AVG(cpu_load) as avg_cpu,
                SUM(rx_bps) as sum_rx,
                SUM(tx_bps) as sum_tx,
                COUNT(*) as sample_count
            ")
            ->groupBy('time_bucket')
            ->orderBy('time_bucket');

        if ($routerId) {
            $query->where('router_id', $routerId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return [];
        }

        return $rows->map(fn ($r) => [
            'time'    => $r->time_bucket,
            'rx'      => (int)$r->sum_rx,
            'tx'      => (int)$r->sum_tx,
            'cpu'     => round((float)$r->avg_cpu, 1),
            'samples' => (int)$r->sample_count,
        ])->toArray();
    }

    /**
     * OLT traffic — only returns data if metric rows with TX/RX metric_key exist.
     * Returns empty array if no OLT bandwidth metrics are stored.
     */
    private function getOltTrafficIfAvailable(string $period, ?int $oltId = null): array
    {
        $txRxKeys = ['tx_bps', 'rx_bps', 'tx_bytes', 'rx_bytes', 'bandwidth_tx', 'bandwidth_rx'];

        // First check if any such metrics exist at all — avoid expensive query if none
        $hasMetrics = OltMetric::whereIn('metric_key', $txRxKeys)
            ->when($oltId, fn ($q) => $q->where('olt_id', $oltId))
            ->exists();

        if (!$hasMetrics) {
            return []; // No OLT bandwidth metrics stored yet
        }

        $since      = $this->periodToDateTime($period);
        $bucketExpr = \App\Services\Support\DbCompat::dateBucket('measured_at', $period);

        $query = DB::table('olt_metrics')
            ->whereIn('metric_key', $txRxKeys)
            ->where('measured_at', '>=', $since)
            ->selectRaw("
                {$bucketExpr} as time_bucket,
                metric_key,
                AVG(metric_value) as avg_value
            ")
            ->groupBy('time_bucket', 'metric_key')
            ->orderBy('time_bucket');

        if ($oltId) {
            $query->where('olt_id', $oltId);
        }

        $rows = $query->get()->groupBy('time_bucket');

        return $rows->map(function ($group, $bucket) {
            $byKey = $group->pluck('avg_value', 'metric_key');
            return [
                'time' => $bucket,
                'rx'   => (float)($byKey['rx_bps'] ?? $byKey['rx_bytes'] ?? $byKey['bandwidth_rx'] ?? 0),
                'tx'   => (float)($byKey['tx_bps'] ?? $byKey['tx_bytes'] ?? $byKey['bandwidth_tx'] ?? 0),
            ];
        })->values()->toArray();
    }

    private function mergeTrafficSeries(array $router, array $olt): array
    {
        // Simple merge by time bucket
        $merged = [];
        $routerByTime = collect($router)->keyBy('time');
        $oltByTime    = collect($olt)->keyBy('time');
        $allTimes     = $routerByTime->keys()->merge($oltByTime->keys())->unique()->sort();

        foreach ($allTimes as $t) {
            $r = $routerByTime->get($t, ['rx' => 0, 'tx' => 0]);
            $o = $oltByTime->get($t, ['rx' => 0, 'tx' => 0]);
            $merged[] = [
                'time' => $t,
                'rx'   => ($r['rx'] ?? 0) + ($o['rx'] ?? 0),
                'tx'   => ($r['tx'] ?? 0) + ($o['tx'] ?? 0),
            ];
        }

        return $merged;
    }

    private function periodToDateTime(string $period): \Carbon\Carbon
    {
        return match($period) {
            '15m'  => now()->subMinutes(15),
            '1h'   => now()->subHour(),
            '6h'   => now()->subHours(6),
            '24h'  => now()->subDay(),
            default => now()->subHour(),
        };
    }

    private function periodToBucket(string $period): string
    {
        $driver = DB::getDriverName();
        $minuteFmt = $driver === 'sqlite' ? '%M' : '%i';

        return match($period) {
            '15m'  => "%Y-%m-%d %H:{$minuteFmt}",
            '1h'   => "%Y-%m-%d %H:{$minuteFmt}",
            '6h'   => '%Y-%m-%d %H:00',
            '24h'  => '%Y-%m-%d %H:00',
            default => "%Y-%m-%d %H:{$minuteFmt}",
        };
    }

    private function formatBps(int $bps): string
    {
        if ($bps === 0) return '0 bps';
        if ($bps < 1_000) return "{$bps} bps";
        if ($bps < 1_000_000) return round($bps / 1_000, 1) . ' Kbps';
        if ($bps < 1_000_000_000) return round($bps / 1_000_000, 1) . ' Mbps';
        return round($bps / 1_000_000_000, 2) . ' Gbps';
    }
}
