<?php

namespace App\Services\Integration;

use Src\Domain\Integration\DriverRegistry;
use Src\Domain\Integration\Drivers\FreeRadiusDriver;
use Src\Domain\Integration\Drivers\MikroTikRouterDriver;
use Src\Domain\Integration\RetryEngine;

class IntegrationService
{
    private DriverRegistry $driverRegistry;

    public function __construct()
    {
        $this->driverRegistry = new DriverRegistry();
        $this->registerDefaultDrivers();
    }

    private function registerDefaultDrivers(): void
    {
        $this->driverRegistry->register(DriverRegistry::TYPE_ROUTER, 'mikrotik', function (array $config) {
            return new MikroTikRouterDriver($config, new RetryEngine(maxRetries: 3, delayMs: 1000));
        });

        $this->driverRegistry->register(DriverRegistry::TYPE_RADIUS, 'freeradius', function (array $config) {
            return new FreeRadiusDriver($config, new RetryEngine(maxRetries: 3, delayMs: 1000));
        });
    }

    public function getDriverRegistry(): DriverRegistry
    {
        return $this->driverRegistry;
    }

    public function getDriver(string $type, string $name, array $config): mixed
    {
        return $this->driverRegistry->get($type, $name, $config);
    }
}
