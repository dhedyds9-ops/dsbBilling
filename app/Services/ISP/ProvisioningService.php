<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;
use App\Models\ISP\ServiceProfile;

interface ProvisioningDriverInterface
{
    public function provision(Onu $onu, ServiceProfile $profile): bool;
    public function deprovision(Onu $onu): bool;
    public function re-provision(Onu $onu, ServiceProfile $profile): bool;
}

class ProvisioningService
{
    protected array $drivers = [];

    public function registerDriver(string $type, ProvisioningDriverInterface $driver): void
    {
        $this->drivers[$type] = $driver;
    }

    public function getDriver(string $type): ?ProvisioningDriverInterface
    {
        return $this->drivers[$type] ?? null;
    }

    public function provision(string $type, Onu $onu, ServiceProfile $profile): bool
    {
        $driver = $this->getDriver($type);
        if ($driver) {
            return $driver->provision($onu, $profile);
        }
        return false;
    }

    public function deprovision(string $type, Onu $onu): bool
    {
        $driver = $this->getDriver($type);
        if ($driver) {
            return $driver->deprovision($onu);
        }
        return false;
    }
}
