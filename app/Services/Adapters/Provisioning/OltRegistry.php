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
        $vendorName = null;

        if ($olt->relationLoaded('vendor') && $olt->vendor !== null) {
            $vendorName = $olt->vendor->name;
        } elseif (!empty($olt->vendor_id)) {
            $vendorRow = \App\Models\ISP\Vendor::select('name')->find($olt->vendor_id);
            if ($vendorRow !== null) {
                $vendorName = $vendorRow->name;
            }
        }

        if ($vendorName === null) {
            $vendorName = 'default';
        }

        $vendorKey = strtolower(trim($vendorName));

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
