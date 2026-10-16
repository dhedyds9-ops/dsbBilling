<?php

namespace App\Services\ISP;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\Router;
use Illuminate\Support\Facades\Cache;

interface DeviceMonitorInterface
{
    public function ping($device): bool;
    public function getSystemInfo($device): array;
    public function getInterfaceStats($device): array;
    public function getTrafficStats($device): array;
    public function getPPPActive($device): array;
    public function getHotspotActive($device): array;
    public function getQueueStats($device): array;
    public function checkAndRepairRadius($device, string $radiusIp, string $radiusSecret): bool;
    
    // Web Winbox Methods
    public function getLogs($device, int $limit = 50): array;
    public function getPppServers($device): array;
    public function getPppProfiles($device): array;
    public function getPppSecrets($device): array;
    public function getVpnServers($device): array;
    public function getHotspotServers($device): array;
    public function getHotspotProfiles($device): array;
    public function getWalledGarden($device): array;
    
    // Web Winbox Phase 2
    public function runTerminalCommand($device, string $command): array;
    public function rebootRouter($device): bool;
    public function backupRouter($device): ?array;
}

class MonitoringService
{
    protected array $drivers = [];

    public function registerDriver(string $deviceType, DeviceMonitorInterface $driver): void
    {
        $this->drivers[$deviceType] = $driver;
    }

    public function getDriver($device): ?DeviceMonitorInterface
    {
        $deviceType = get_class($device);
        return $this->drivers[$deviceType] ?? null;
    }

    public function ping($device): bool
    {
        $cachedData = self::getCachedData($device);
        if (!empty($cachedData['is_online'])) {
            return true;
        }

        // If device is a Router and cache expired or not found, check live reachability
        if ($device instanceof \App\Models\ISP\Router) {
            // Check if seen very recently (within 15 minutes)
            if ($device->last_seen_at && $device->last_seen_at->diffInMinutes(now()) <= 15) {
                return true;
            }

            // Perform fast socket probe if IP address exists (timeout 1.0s)
            if (!empty($device->ip_address)) {
                $port = $device->api_port ?: ($device->port ?: 8728);
                $fp = @fsockopen($device->ip_address, (int)$port, $errno, $errstr, 1.0);
                if ($fp) {
                    fclose($fp);
                    $cachedData['is_online'] = true;
                    $cachedData['last_updated'] = now()->timestamp;
                    Cache::put(self::getCacheKey($device), $cachedData, now()->addMinutes(15));
                    return true;
                }
            }
        }

        return false;
    }

    public function getSystemInfo($device): array
    {
        $cachedData = self::getCachedData($device);
        return $cachedData['system_info'] ?? [];
    }

    public function getInterfaceStats($device): array
    {
        $driver = $this->getDriver($device);
        if ($driver) {
            return $driver->getInterfaceStats($device);
        }
        return [];
    }

    public function getTrafficStats($device): array
    {
        $driver = $this->getDriver($device);
        if ($driver) {
            return $driver->getTrafficStats($device);
        }
        return [];
    }

    public function getPPPActive($device): array
    {
        $cachedData = self::getCachedData($device);
        return $cachedData['ppp_sessions'] ?? [];
    }

    public function getHotspotActive($device): array
    {
        $cachedData = self::getCachedData($device);
        return $cachedData['hotspot_sessions'] ?? [];
    }
    
    public function getQueueStats($device): array
    {
        $cachedData = self::getCachedData($device);
        return $cachedData['queue_stats'] ?? [];
    }
    
    // Web Winbox Methods (Direct API calls for real-time visibility, not cached for now)
    
    public function getLogs($device, int $limit = 50): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getLogs($device, $limit) : [];
    }

    public function getPppServers($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getPppServers($device) : [];
    }

    public function getPppProfiles($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getPppProfiles($device) : [];
    }

    public function getPppSecrets($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getPppSecrets($device) : [];
    }

    public function getVpnServers($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getVpnServers($device) : [];
    }

    public function getHotspotServers($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getHotspotServers($device) : [];
    }

    public function getHotspotProfiles($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getHotspotProfiles($device) : [];
    }

    public function getWalledGarden($device): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->getWalledGarden($device) : [];
    }

    // Web Winbox Phase 2
    public function runTerminalCommand($device, string $command): array
    {
        return ($driver = $this->getDriver($device)) ? $driver->runTerminalCommand($device, $command) : [];
    }
    
    public function rebootRouter($device): bool
    {
        return ($driver = $this->getDriver($device)) ? $driver->rebootRouter($device) : false;
    }
    
    public function backupRouter($device): ?array
    {
        return ($driver = $this->getDriver($device)) ? $driver->backupRouter($device) : null;
    }
    
    public static function getCacheKey($device): string
    {
        $deviceId = $device->id ?? 0;
        $deviceType = self::getDeviceType($device);
        $tenant = 'central';
        if (request()) {
            $host = request()->getHost();
            $tenant = explode('.', $host)[0] ?? 'central';
        }
        
        return "monitoring:{$tenant}:{$deviceType}:{$deviceId}";
    }
    
    public static function getDeviceType($device): string
    {
        $class = get_class($device);
        
        return match ($class) {
            \App\Models\ISP\Router::class => 'router',
            \App\Models\ISP\Olt::class => 'olt',
            \App\Models\ISP\Onu::class => 'onu',
            default => strtolower(class_basename($class)),
        };
    }
    
    public function getHealthScore($device): ?int
    {
        $cachedData = self::getCachedData($device);
        return $cachedData['health_score'] ?? null;
    }
    
    public static function getCachedData($device): array
    {
        $cacheKey = self::getCacheKey($device);
        
        return Cache::get($cacheKey, [
            'is_online' => false,
            'system_info' => [],
            'ppp_sessions' => [],
            'hotspot_sessions' => [],
            'queue_stats' => [],
            'last_updated' => null,
        ]);
    }
}
