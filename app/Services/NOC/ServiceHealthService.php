<?php

namespace App\Services\NOC;

use App\Models\ISP\Olt;
use App\Models\ISP\RadiusServer;
use App\Models\Provisioning\ProvisionPipeline;
use Illuminate\Support\Facades\Cache;

class ServiceHealthService
{
    const CACHE_TTL = 30; // seconds

    /**
     * Get all service health statuses.
     * Returns array of service health checks — never exposes credentials.
     */
    public function getAllServiceHealth(): array
    {
        return Cache::remember('noc.service.health', self::CACHE_TTL, function () {
            return [
                $this->checkRadiusHealth(),
                $this->checkOltNetworkHealth(),
                $this->checkProvisioningQueueHealth(),
                $this->checkGenieAcsHealth(),
            ];
        });
    }

    /**
     * Check RADIUS server health.
     * Checks if RADIUS servers are configured and attempts TCP connection.
     * NEVER exposes radius_secret or credentials.
     */
    public function checkRadiusHealth(): array
    {
        $servers = RadiusServer::active()
            ->select('id', 'name', 'ip_address', 'auth_port', 'status')
            ->get();

        if ($servers->isEmpty()) {
            return [
                'name'   => 'RADIUS',
                'status' => 'unknown',
                'detail' => 'No RADIUS servers configured',
                'icon'   => 'radio',
            ];
        }

        $reachable = 0;
        $total     = $servers->count();

        foreach ($servers as $server) {
            try {
                $socket = @fsockopen(
                    $server->ip_address,
                    $server->auth_port ?? 1812,
                    $errno,
                    $errstr,
                    2 // 2 second timeout
                );
                if ($socket) {
                    fclose($socket);
                    $reachable++;
                }
            } catch (\Throwable) {
                // unreachable
            }
        }

        if ($reachable === $total) {
            return [
                'name'   => 'RADIUS',
                'status' => 'healthy',
                'detail' => "{$reachable}/{$total} servers reachable",
                'icon'   => 'radio',
            ];
        }

        if ($reachable > 0) {
            return [
                'name'   => 'RADIUS',
                'status' => 'warning',
                'detail' => "{$reachable}/{$total} servers reachable",
                'icon'   => 'radio',
            ];
        }

        return [
            'name'   => 'RADIUS',
            'status' => 'down',
            'detail' => "0/{$total} servers reachable",
            'icon'   => 'radio',
        ];
    }

    /**
     * Check OLT network health.
     * Based on last_polled_at recency — no live SNMP queries.
     */
    public function checkOltNetworkHealth(): array
    {
        $total   = Olt::withoutTrashed()->count();
        $staleAt = now()->subMinutes(15);

        if ($total === 0) {
            return [
                'name'   => 'OLT Network',
                'status' => 'unknown',
                'detail' => 'No OLTs configured',
                'icon'   => 'server-stack',
            ];
        }

        $stale   = Olt::withoutTrashed()
            ->where(function ($q) use ($staleAt) {
                $q->whereNull('last_polled_at')
                  ->orWhere('last_polled_at', '<', $staleAt);
            })->count();

        $online  = $total - $stale;

        if ($stale === 0) {
            return [
                'name'   => 'OLT Network',
                'status' => 'healthy',
                'detail' => "All {$total} OLTs polled recently",
                'icon'   => 'server-stack',
            ];
        }

        if ($stale === $total) {
            return [
                'name'   => 'OLT Network',
                'status' => 'down',
                'detail' => "All {$total} OLTs not responding",
                'icon'   => 'server-stack',
            ];
        }

        return [
            'name'   => 'OLT Network',
            'status' => 'warning',
            'detail' => "{$stale}/{$total} OLTs not polled recently",
            'icon'   => 'server-stack',
        ];
    }

    /**
     * Check provisioning queue health.
     */
    public function checkProvisioningQueueHealth(): array
    {
        $pending = ProvisionPipeline::withoutTrashed()
            ->whereIn('status', ['pending', 'running'])
            ->count();

        $failed = ProvisionPipeline::withoutTrashed()
            ->where('status', 'failed')
            ->whereDate('created_at', '>=', now()->subDay())
            ->count();

        if ($pending > 50) {
            return [
                'name'   => 'Provisioning Queue',
                'status' => 'warning',
                'detail' => "{$pending} jobs queued/running, {$failed} failed (24h)",
                'icon'   => 'list-checks',
            ];
        }

        if ($failed > 10) {
            return [
                'name'   => 'Provisioning Queue',
                'status' => 'warning',
                'detail' => "{$failed} failed jobs in last 24h",
                'icon'   => 'list-checks',
            ];
        }

        $status = $pending > 0 ? 'healthy' : 'healthy';
        $detail = $pending > 0
            ? "{$pending} jobs queued/running"
            : 'No pending jobs';

        if ($failed > 0) {
            $detail .= ", {$failed} failed (24h)";
        }

        return [
            'name'   => 'Provisioning Queue',
            'status' => $status,
            'detail' => $detail,
            'icon'   => 'list-checks',
        ];
    }

    /**
     * Check GenieACS health.
     * Uses ACS device table recency — no direct ACS credential exposure.
     */
    public function checkGenieAcsHealth(): array
    {
        // Check via ACS devices table last_seen
        $hasAcsDevices = false;

        try {
            $hasAcsDevices = \Illuminate\Support\Facades\DB::table('acs_devices')->exists();
        } catch (\Throwable) {
            return [
                'name'   => 'GenieACS',
                'status' => 'unknown',
                'detail' => 'ACS not configured',
                'icon'   => 'router',
            ];
        }

        if (!$hasAcsDevices) {
            return [
                'name'   => 'GenieACS',
                'status' => 'unknown',
                'detail' => 'No ACS devices registered',
                'icon'   => 'router',
            ];
        }

        // Check if ACS URL is configured via Setting model
        $acsUrl = null;
        try {
            $acsUrl = \App\Models\Setting::where('key', 'acs_url')
                ->orWhere('key', 'genieacs_url')
                ->value('value');
        } catch (\Throwable) {
            // Setting model may not have this key
        }

        if ($acsUrl) {
            try {
                $ch = curl_init($acsUrl . '/ping');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 3,
                    CURLOPT_NOBODY         => true,
                ]);
                curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 500) {
                    return [
                        'name'   => 'GenieACS',
                        'status' => 'healthy',
                        'detail' => 'ACS server reachable',
                        'icon'   => 'router',
                    ];
                }

                return [
                    'name'   => 'GenieACS',
                    'status' => 'down',
                    'detail' => "ACS returned HTTP {$httpCode}",
                    'icon'   => 'router',
                ];
            } catch (\Throwable) {
                return [
                    'name'   => 'GenieACS',
                    'status' => 'down',
                    'detail' => 'ACS server unreachable',
                    'icon'   => 'router',
                ];
            }
        }

        // Fallback: check device last_seen recency
        $staleCount = \Illuminate\Support\Facades\DB::table('acs_devices')
            ->where('last_inform', '<', now()->subHours(24))
            ->count();

        $totalDevices = \Illuminate\Support\Facades\DB::table('acs_devices')->count();

        if ($staleCount > $totalDevices / 2) {
            return [
                'name'   => 'GenieACS',
                'status' => 'warning',
                'detail' => "{$staleCount}/{$totalDevices} devices not seen in 24h",
                'icon'   => 'router',
            ];
        }

        return [
            'name'   => 'GenieACS',
            'status' => 'healthy',
            'detail' => "{$totalDevices} ACS devices registered",
            'icon'   => 'router',
        ];
    }
}
