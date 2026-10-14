<?php

namespace App\Jobs\Monitoring;

use App\Models\ISP\Router;
use App\Models\ISP\RouterMonitoringLog;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\QueueMonitoringLog;
use App\Services\Adapters\Monitoring\MikroTikDriver;
use App\Services\ISP\MonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class PollSingleRouterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Router $router
    ) {}

    public function handle(MikroTikDriver $driver)
    {
        try {
            $isOnline = $driver->ping($this->router);
            
            if (!$isOnline) {
                $this->saveMonitoringLog([
                    'is_online' => false,
                    'error_message' => 'Router is offline',
                ]);
                return;
            }
            
            // Get system info
            $systemInfo = $driver->getSystemInfo($this->router);
            
            // Fetch traffic from interfaces
            $interfaceStats = $driver->getInterfaceStats($this->router);
            $totalRxBps = 0;
            $totalTxBps = 0;
            foreach ($interfaceStats as $iface) {
                $totalRxBps += (int)($iface['rx-bps'] ?? 0);
                $totalTxBps += (int)($iface['tx-bps'] ?? 0);
            }
            $finalRx = (int)($totalRxBps / 2);
            $finalTx = (int)($totalTxBps / 2);

            // Get PPP Active Sessions
            $pppSessions = $driver->getPPPActive($this->router);
            $this->updatePppSessions($pppSessions);
            
            // Get Hotspot Active Sessions
            $hotspotSessions = $driver->getHotspotActive($this->router);
            $this->updateHotspotSessions($hotspotSessions);
            
            // Get Queue Stats
            $queueStats = $driver->getQueueStats($this->router);
            $this->updateQueueStats($queueStats);

            if ($finalRx === 0 && $finalTx === 0) {
                foreach ($queueStats as $q) {
                    $finalRx += (int)($q['rate_down'] ?? 0);
                    $finalTx += (int)($q['rate_up'] ?? 0);
                }
                if ($finalRx === 0 && $finalTx === 0) {
                    foreach ($pppSessions as $s) {
                        $finalRx += (int)($s['rate_down'] ?? 0);
                        $finalTx += (int)($s['rate_up'] ?? 0);
                    }
                }
            }
            
            // Save monitoring log
            $this->saveMonitoringLog([
                'is_online' => true,
                'identity' => $systemInfo['identity'] ?? null,
                'version' => $systemInfo['version'] ?? null,
                'cpu' => $systemInfo['cpu'] ?? null,
                'cpu_load' => $systemInfo['cpu_load'] ?? 0,
                'free_memory' => $systemInfo['free_memory'] ?? 0,
                'total_memory' => $systemInfo['total_memory'] ?? 0,
                'uptime' => $systemInfo['uptime'] ?? null,
                'error_message' => $systemInfo['error'] ?? null,
                'rx_bps' => $finalRx,
                'tx_bps' => $finalTx,
            ]);
            
            // Update router status, last seen at and version
            $updateData = [
                'last_seen_at' => now(),
                'routeros_version' => $systemInfo['version'] ?? null,
            ];
            if ($this->router->status !== 'inactive' && $this->router->status !== Router::STATUS_DISABLED) {
                $updateData['status'] = 'active';
            }
            $this->router->update($updateData);
            
            // Auto Recovery: Check and Repair RADIUS Configuration
            $radiusHost = parse_url(config('app.url'), PHP_URL_HOST) ?? '127.0.0.1';
            $driver->checkAndRepairRadius($this->router, $radiusHost, $this->router->radius_secret ?? 'radius_secret_here');
            
            // Cache all data
            $cacheKey = MonitoringService::getCacheKey($this->router);
            $cacheData = [
                'system_info' => $systemInfo,
                'ppp_sessions' => $pppSessions,
                'hotspot_sessions' => $hotspotSessions,
                'queue_stats' => $queueStats,
                'is_online' => true,
                'last_updated' => now()->timestamp,
            ];
            Cache::put($cacheKey, $cacheData, now()->addSeconds(300));
            
        } catch (\Exception $e) {
            $this->saveMonitoringLog([
                'is_online' => false,
                'error_message' => $e->getMessage(),
            ]);
            
            // Update cache with error
            $cacheKey = MonitoringService::getCacheKey($this->router);
            Cache::put($cacheKey, [
                'is_online' => false,
                'error_message' => $e->getMessage(),
                'last_updated' => now()->timestamp,
            ], now()->addSeconds(300));
        }
    }
    
    private function saveMonitoringLog(array $data)
    {
        RouterMonitoringLog::create([
            'router_id' => $this->router->id,
            ...$data
        ]);
    }
    
    private function updatePppSessions(array $sessions)
    {
        // Delete old sessions
        PppActiveSession::where('router_id', $this->router->id)->delete();
        
        // Insert new sessions
        foreach ($sessions as $session) {
            PppActiveSession::create([
                'router_id' => $this->router->id,
                'name' => $session['name'] ?? null,
                'service' => $session['service'] ?? null,
                'caller_id' => $session['caller_id'] ?? null,
                'address' => $session['address'] ?? null,
                'uptime' => $session['uptime'] ?? null,
                'bytes_in' => $session['bytes_in'] ?? 0,
                'bytes_out' => $session['bytes_out'] ?? 0,
                'packets_in' => $session['packets_in'] ?? 0,
                'packets_out' => $session['packets_out'] ?? 0,
                'rate_up' => $session['rate_up'] ?? null,
                'rate_down' => $session['rate_down'] ?? null,
                'session_started_at' => null,
            ]);
        }

        try {
            app(\App\Services\ISP\Session\OnlineSessionStore::class)->upsertPppoeFromRouter($this->router, $sessions);
        } catch (\Throwable $e) {}
    }
    
    private function updateHotspotSessions(array $sessions)
    {
        // Delete old sessions
        HotspotActiveSession::where('router_id', $this->router->id)->delete();
        
        // Insert new sessions
        foreach ($sessions as $session) {
            HotspotActiveSession::create([
                'router_id' => $this->router->id,
                'user' => $session['user'] ?? null,
                'mac_address' => $session['mac_address'] ?? null,
                'address' => $session['address'] ?? null,
                'server' => $session['server'] ?? null,
                'login_by' => $session['login_by'] ?? null,
                'uptime' => $session['uptime'] ?? null,
                'bytes_in' => $session['bytes_in'] ?? 0,
                'bytes_out' => $session['bytes_out'] ?? 0,
                'session_started_at' => null,
            ]);
        }
    }
    
    private function updateQueueStats(array $queues)
    {
        // Delete old queue stats
        QueueMonitoringLog::where('router_id', $this->router->id)->delete();
        
        // Insert new queue stats
        foreach ($queues as $queue) {
            QueueMonitoringLog::create([
                'router_id' => $this->router->id,
                'queue_name' => $queue['queue_name'] ?? null,
                'target' => $queue['target'] ?? null,
                'max_limit' => $queue['max_limit'] ?? null,
                'burst_limit' => $queue['burst_limit'] ?? null,
                'limit_at' => $queue['limit_at'] ?? null,
                'bytes_in' => $queue['bytes_in'] ?? 0,
                'bytes_out' => $queue['bytes_out'] ?? 0,
                'packets_in' => $queue['packets_in'] ?? 0,
                'packets_out' => $queue['packets_out'] ?? 0,
                'rate_up' => $queue['rate_up'] ?? null,
                'rate_down' => $queue['rate_down'] ?? null,
            ]);
        }
    }
}
