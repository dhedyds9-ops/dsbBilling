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
        return $cachedData['is_online'] ?? false;
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
    
    public static function getCacheKey($device): string
    {
        $deviceId = $device->id ?? 0;
        $deviceType = self::getDeviceType($device);
        
        return "monitoring:{$deviceType}:{$deviceId}";
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
