<?php

namespace App\Services\Adapters\Provisioning;

use App\Models\ISP\Olt;
use App\Services\Adapters\BaseAdapterRegistry;
use App\Services\Adapters\Provisioning\Contracts\OltDriverInterface;
use InvalidArgumentException;

/**
 * @method OltDriverInterface resolve(string $key, mixed ...$args)
 */
class OltRegistry extends BaseAdapterRegistry
{
    public function __construct()
    {
        $drivers = config('olt-drivers.drivers', []);
        foreach ($drivers as $vendorKey => $driverClass) {
            $this->register($vendorKey, $driverClass);
        }
    }

    public function forOlt(Olt $olt): OltDriverInterface
    {
        $vendorName = strtolower($olt->vendor?->name ?? 'default');
        $vendorKey = trim($vendorName);

        if ($this->has($vendorKey)) {
            $class = $this->adapters[$vendorKey];
            return new $class($olt);
        }

        $fuzzy = $this->fuzzyMatch($vendorKey);
        if ($fuzzy !== null) {
            $class = $this->adapters[$fuzzy];
            return new $class($olt);
        }

        $defaultClass = config('olt-drivers.drivers.default');
        if (!$defaultClass) {
            throw new InvalidArgumentException("OLT driver tidak ditemukan untuk vendor: {$vendorKey}");
        }
        return new $defaultClass($olt);
    }

    protected function fuzzyMatch(string $vendor): ?string
    {
        $lower = strtolower($vendor);
        foreach (array_keys($this->adapters) as $key) {
            if (str_contains($lower, $key) || str_contains($key, $lower)) {
                return $key;
            }
        }
        return null;
    }
}
