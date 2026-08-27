<?php

namespace App\Services\Monitoring\Collectors;

use App\Models\ISP\Router;
use App\Services\Monitoring\BaseCollector;
use App\Services\Adapters\Monitoring\MikroTikDriver;
use App\Models\ISP\RouterMonitoringLog;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\QueueMonitoringLog;
use App\Services\ISP\Session\OnlineSessionStore;
use App\Services\Monitoring\AlertEngine;
use App\Support\UptimeParser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RouterCollector extends BaseCollector
{
    public function __construct(
        private MikroTikDriver $driver,
        private AlertEngine $alertEngine,
        private OnlineSessionStore $onlineSessionStore,
    ) {}
    
    public function collect(): void
    {
        $activeRouters = Router::active()->get();
        
        foreach ($activeRouters as $router) {
            $this->collectSingleRouter($router);
        }
    }
    
    public function getName(): string
    {
        return 'router';
    }
    
    private function collectSingleRouter(Router $router): void
    {
        try {
            $isOnline = $this->driver->ping($router);
            
            if (!$isOnline) {
                $this->saveMonitoringLog($router, [
                    'is_online' => false,
                    'error_message' => 'Router is offline',
                ]);
                
                $this->updateCache($router, false, 'Router is offline');
                
                $this->alertEngine->createAlert(
                    'critical',
                    $router,
                    'Router is offline',
                    "Router {$router->name} is not responding to pings",
                    ['ip' => $router->ip_address]
                );
                
                return;
            }
            
            $systemInfo = $this->driver->getSystemInfo($router);
            $pppSessions = $this->driver->getPPPActive($router);
            $hotspotSessions = $this->driver->getHotspotActive($router);
            $queueStats = $this->driver->getQueueStats($router);
            
            $healthScore = $this->calculateHealthScore($systemInfo);
            
            // Save monitoring log
            $this->saveMonitoringLog($router, [
                'is_online' => true,
                'identity' => $systemInfo['identity'] ?? null,
                'version' => $systemInfo['version'] ?? null,
                'cpu' => $systemInfo['cpu'] ?? null,
                'cpu_load' => $systemInfo['cpu_load'] ?? 0,
                'free_memory' => $systemInfo['free_memory'] ?? 0,
                'total_memory' => $systemInfo['total_memory'] ?? 0,
                'uptime' => $systemInfo['uptime'] ?? null,
                'error_message' => $systemInfo['error'] ?? null,
            ]);
            
            $router->update([
                'last_seen_at' => now(),
                'routeros_version' => $systemInfo['version'] ?? null,
            ]);
            
            $this->updatePppSessions($router, $pppSessions);
            $this->updateHotspotSessions($router, $hotspotSessions);
            $this->updateQueueStats($router, $queueStats);
            
            // Update cache with standardized key
            $this->updateCache($router, true, null, [
                'system_info' => $systemInfo,
                'ppp_sessions' => $pppSessions,
                'hotspot_sessions' => $hotspotSessions,
                'queue_stats' => $queueStats,
                'health_score' => $healthScore,
                'last_updated' => now()->timestamp,
            ]);
            
            // Check for alerts
            $this->checkAlerts($router, $systemInfo, $healthScore);
            
        } catch (\Exception $e) {
            $this->saveMonitoringLog($router, [
                'is_online' => false,
                'error_message' => $e->getMessage(),
            ]);
            
            $this->updateCache($router, false, $e->getMessage());
            
            $this->alertEngine->createAlert(
                'warning',
                $router,
                'Router monitoring error',
                "Monitoring for router {$router->name} failed: {$e->getMessage()}",
                ['exception' => $e->getMessage()]
            );
        }
    }
    
    private function saveMonitoringLog(Router $router, array $data): void
    {
        RouterMonitoringLog::create([
            'router_id' => $router->id,
            ...$data
        ]);
    }
    
    private function updatePppSessions(Router $router, array $sessions): void
    {
        $compositeKeys = [];
        $now = now();

        DB::transaction(function () use ($router, $sessions, &$compositeKeys, $now) {
            foreach ($sessions as $session) {
                $name = (string)($session['name'] ?? '');
                $callerId = (string)($session['caller_id'] ?? '');
                $address = (string)($session['address'] ?? '');
                $uptime = (string)($session['uptime'] ?? '');
                $startedAt = UptimeParser::toDateTime($uptime);

                $key = "{$router->id}|{$name}|{$callerId}|{$address}";
                $compositeKeys[] = $key;

                PppActiveSession::query()->updateOrCreate(
                    [
                        'router_id' => $router->id,
                        'name' => $name,
                        'caller_id' => $callerId,
                        'address' => $address,
                    ],
                    [
                        'service' => $session['service'] ?? null,
                        'uptime' => $uptime,
                        'bytes_in' => (int)($session['bytes_in'] ?? 0),
                        'bytes_out' => (int)($session['bytes_out'] ?? 0),
                        'packets_in' => (int)($session['packets_in'] ?? 0),
                        'packets_out' => (int)($session['packets_out'] ?? 0),
                        'rate_up' => $session['rate_up'] ?? null,
                        'rate_down' => $session['rate_down'] ?? null,
                        'session_started_at' => $startedAt,
                        'updated_at' => $now,
                    ]
                );
            }

            if (count($compositeKeys) > 0) {
                $existing = PppActiveSession::query()
                    ->where('router_id', $router->id)
                    ->get(['id', 'router_id', 'name', 'caller_id', 'address']);

                $toDeleteIds = $existing->filter(function ($row) use ($compositeKeys) {
                    $k = "{$row->router_id}|{$row->name}|{$row->caller_id}|{$row->address}";
                    return !in_array($k, $compositeKeys, true);
                })->pluck('id')->all();

                if (count($toDeleteIds) > 0) {
                    PppActiveSession::query()->whereIn('id', $toDeleteIds)->delete();
                }
            } else {
                PppActiveSession::query()->where('router_id', $router->id)->delete();
            }
        });

        try {
            $this->onlineSessionStore->upsertPppoeFromRouter($router, $sessions);
        } catch (\Throwable $e) {
            Cache::driver('array');
            \Illuminate\Support\Facades\Log::warning('OnlineSession PPPoE sync non-fatal', ['router' => $router->name, 'err' => $e->getMessage()]);
        }
    }
    
    private function updateHotspotSessions(Router $router, array $sessions): void
    {
        $compositeKeys = [];
        $now = now();

        DB::transaction(function () use ($router, $sessions, &$compositeKeys, $now) {
            foreach ($sessions as $session) {
                $user = (string)($session['user'] ?? '');
                $mac = (string)($session['mac_address'] ?? '');
                $address = (string)($session['address'] ?? '');
                $server = (string)($session['server'] ?? '');
                $uptime = (string)($session['uptime'] ?? '');
                $startedAt = UptimeParser::toDateTime($uptime);

                $key = "{$router->id}|{$user}|{$mac}|{$address}|{$server}";
                $compositeKeys[] = $key;

                HotspotActiveSession::query()->updateOrCreate(
                    [
                        'router_id' => $router->id,
                        'user' => $user,
                        'mac_address' => $mac,
                        'address' => $address,
                        'server' => $server,
                    ],
                    [
                        'login_by' => $session['login_by'] ?? null,
                        'uptime' => $uptime,
                        'bytes_in' => (int)($session['bytes_in'] ?? 0),
                        'bytes_out' => (int)($session['bytes_out'] ?? 0),
                        'session_started_at' => $startedAt,
                        'updated_at' => $now,
                    ]
                );
            }

            if (count($compositeKeys) > 0) {
                $existing = HotspotActiveSession::query()
                    ->where('router_id', $router->id)
                    ->get(['id', 'router_id', 'user', 'mac_address', 'address', 'server']);

                $toDeleteIds = $existing->filter(function ($row) use ($compositeKeys) {
                    $k = "{$row->router_id}|{$row->user}|{$row->mac_address}|{$row->address}|{$row->server}";
                    return !in_array($k, $compositeKeys, true);
                })->pluck('id')->all();

                if (count($toDeleteIds) > 0) {
                    HotspotActiveSession::query()->whereIn('id', $toDeleteIds)->delete();
                }
            } else {
                HotspotActiveSession::query()->where('router_id', $router->id)->delete();
            }
        });

        try {
            $this->onlineSessionStore->upsertHotspotFromRouter($router, $sessions);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OnlineSession Hotspot sync non-fatal', ['router' => $router->name, 'err' => $e->getMessage()]);
        }
    }
    
    private function updateQueueStats(Router $router, array $queues): void
    {
        QueueMonitoringLog::where('router_id', $router->id)->delete();
        
        foreach ($queues as $queue) {
            QueueMonitoringLog::create([
                'router_id' => $router->id,
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
    
    private function updateCache(Router $router, bool $isOnline, ?string $error = null, array $data = []): void
    {
        $cacheKey = $this->getCacheKey('router', $router->id);
        $cacheData = [
            'is_online' => $isOnline,
            'error_message' => $error,
            ...$data,
        ];
        
        $this->putToCache($cacheKey, $cacheData);
    }
    
    private function calculateHealthScore(array $systemInfo): int
    {
        $score = 100;
        
        // CPU load penalty
        $cpuLoad = $systemInfo['cpu_load'] ?? 0;
        if ($cpuLoad > 90) {
            $score -= 30;
        } elseif ($cpuLoad > 70) {
            $score -= 15;
        } elseif ($cpuLoad > 50) {
            $score -= 5;
        }
        
        // Memory usage penalty
        $freeMemory = $systemInfo['free_memory'] ?? 0;
        $totalMemory = $systemInfo['total_memory'] ?? 1;
        if ($totalMemory > 0) {
            $memoryUsagePercent = (($totalMemory - $freeMemory) / $totalMemory) * 100;
            
            if ($memoryUsagePercent > 90) {
                $score -= 20;
            } elseif ($memoryUsagePercent > 70) {
                $score -= 10;
            } elseif ($memoryUsagePercent > 50) {
                $score -= 5;
            }
        }
        
        return max(0, $score);
    }
    
    private function checkAlerts(Router $router, array $systemInfo, int $healthScore): void
    {
        // CPU load alert
        $cpuLoad = $systemInfo['cpu_load'] ?? 0;
        if ($cpuLoad > 90) {
            $this->alertEngine->createAlert(
                'critical',
                $router,
                'High CPU usage',
                "Router {$router->name} has CPU load at {$cpuLoad}%",
                ['cpu_load' => $cpuLoad]
            );
        } elseif ($cpuLoad > 70) {
            $this->alertEngine->createAlert(
                'warning',
                $router,
                'Elevated CPU usage',
                "Router {$router->name} has CPU load at {$cpuLoad}%",
                ['cpu_load' => $cpuLoad]
            );
        }
        
        // Memory usage alert
        $freeMemory = $systemInfo['free_memory'] ?? 0;
        $totalMemory = $systemInfo['total_memory'] ?? 1;
        if ($totalMemory > 0) {
            $memoryUsagePercent = round((($totalMemory - $freeMemory) / $totalMemory) * 100);
            
            if ($memoryUsagePercent > 90) {
                $this->alertEngine->createAlert(
                    'critical',
                    $router,
                    'High memory usage',
                    "Router {$router->name} has memory usage at {$memoryUsagePercent}%",
                    ['memory_usage' => $memoryUsagePercent]
                );
            } elseif ($memoryUsagePercent > 70) {
                $this->alertEngine->createAlert(
                    'warning',
                    $router,
                    'Elevated memory usage',
                    "Router {$router->name} has memory usage at {$memoryUsagePercent}%",
                    ['memory_usage' => $memoryUsagePercent]
                );
            }
        }
        
        // Health score alerts
        if ($healthScore < 70) {
            $this->alertEngine->createAlert(
                'critical',
                $router,
                'Low health score',
                "Router {$router->name} has health score at {$healthScore}",
                ['health_score' => $healthScore]
            );
        } elseif ($healthScore < 85) {
            $this->alertEngine->createAlert(
                'warning',
                $router,
                'Reduced health score',
                "Router {$router->name} has health score at {$healthScore}",
                ['health_score' => $healthScore]
            );
        }
    }
}
