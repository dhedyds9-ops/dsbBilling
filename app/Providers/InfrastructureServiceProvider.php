<?php

namespace App\Providers;

use App\Integration\Maps\Drivers\GoogleMapsDriver;
use App\Integration\Maps\Drivers\OpenStreetMapDriver;
use App\Integration\Maps\Services\MapsService;
use App\Integration\MikroTik\Services\RouterOSService;
use App\Integration\Notification\Services\NotificationService;
use App\Models\ISP\Router;
use App\Services\ConfigurationEngine;
use App\Services\ISP\MonitoringService;
use App\Services\Monitoring\AlertEngine;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\Adapters\Maps\MapsAdapterRegistry;
use App\Services\Adapters\Monitoring\MonitoringDriverRegistry;
use App\Services\ISP\OltPollingService;
use App\Services\ISP\OdpOccupancyService;
use App\Services\ISP\GenieAcsProvisioningService;
use App\Services\ISP\FiberLinkStatusService;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Integration\RetryEngine;

class InfrastructureServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ConfigurationEngine::class => ConfigurationEngine::class,
        AlertEngine::class => AlertEngine::class,
        MapsService::class => MapsService::class,
        RouterOSService::class => RouterOSService::class,
        NotificationService::class => NotificationService::class,
        MonitoringService::class => MonitoringService::class,
        OltRegistry::class => OltRegistry::class,
        MapsAdapterRegistry::class => MapsAdapterRegistry::class,
        MonitoringDriverRegistry::class => MonitoringDriverRegistry::class,
        OltPollingService::class => OltPollingService::class,
        OdpOccupancyService::class => OdpOccupancyService::class,
        GenieAcsProvisioningService::class => GenieAcsProvisioningService::class,
        FiberLinkStatusService::class => FiberLinkStatusService::class,
    ];

    public function register(): void
    {
        foreach ($this->singletons as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        $this->app->singleton(GoogleMapsDriver::class, function ($app) {
            return new GoogleMapsDriver(config('services.google.maps.key', ''));
        });

        $this->app->singleton(OpenStreetMapDriver::class, function () {
            return new OpenStreetMapDriver();
        });
    }

    public function boot(): void
    {
        // Keep compatibility with existing MonitoringService
        $monitoringService = $this->app->make(MonitoringService::class);
        $oldMikroTikDriver = new class implements \App\Services\ISP\DeviceMonitorInterface {
            private ?\App\Models\ISP\Router $router = null;

            public function ping($device): bool
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->ping();
            }

            public function getSystemInfo($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getSystemInfo();
            }

            public function getInterfaceStats($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getInterfaceStats();
            }

            public function getTrafficStats($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getTrafficStats();
            }

            public function getPPPActive($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getPPPActive();
            }

            public function getHotspotActive($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getHotspotActive();
            }

            public function getQueueStats($device): array
            {
                $this->router = $device;
                $driver = app(\App\Integration\MikroTik\Services\RouterOSService::class)->getDriver($device);
                return $driver->getQueueStats();
            }
        };
        $monitoringService->registerDriver(Router::class, $oldMikroTikDriver);
    }
}
