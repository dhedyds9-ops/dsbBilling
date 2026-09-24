<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use App\Models\ISP\Splitter;
use App\Models\ISP\Vlan;
use App\Models\ISP\IpPool;
use App\Models\Provisioning\CapacityManagement;
use App\Repositories\Provisioning\CapacityManagementRepository;
use Illuminate\Support\Facades\Log;

class CapacityManagementService
{
    public function __construct(
        protected CapacityManagementRepository $capacityRepository,
    ) {}

    public function calculateOltCapacity(Olt $olt): CapacityManagement
    {
        $totalPorts = $olt->ponPorts()->count();
        $usedPorts = $olt->ponPorts()->whereHas('portReservations', function($q) {
            $q->where('status', 'active');
        })->count();
        $availablePorts = $totalPorts - $usedPorts;

        return $this->updateOrCreateCapacity('olt', $olt->id, $totalPorts, $usedPorts, $availablePorts);
    }

    public function calculateOdpCapacity(Odp $odp): CapacityManagement
    {
        $totalPorts = $odp->ports ?? 0;
        $usedPorts = $odp->surveys()->count();
        $availablePorts = $totalPorts - $usedPorts;

        return $this->updateOrCreateCapacity('odp', $odp->id, $totalPorts, $usedPorts, $availablePorts);
    }

    public function calculateSplitterCapacity(Splitter $splitter): CapacityManagement
    {
        $totalPorts = $splitter->ports ?? 0;
        $usedPorts = $splitter->surveys()->count();
        $availablePorts = $totalPorts - $usedPorts;

        return $this->updateOrCreateCapacity('splitter', $splitter->id, $totalPorts, $usedPorts, $availablePorts);
    }

    public function calculateVlanCapacity(Vlan $vlan): CapacityManagement
    {
        $total = $vlan->max_usage ?? 1000;
        $used = $vlan->vlanAllocations()->where('status', 'active')->count();
        $available = $total - $used;

        return $this->updateOrCreateCapacity('vlan', $vlan->id, $total, $used, $available);
    }

    public function calculateIpPoolCapacity(IpPool $pool): CapacityManagement
    {
        $total = $pool->total_ips ?? 256;
        $used = $pool->ipAllocations()->where('status', 'active')->count();
        $available = $total - $used;

        return $this->updateOrCreateCapacity('ip_pool', $pool->id, $total, $used, $available);
    }

    public function checkCapacityAvailable(string $resourceType, int $resourceId): bool
    {
        $capacity = $this->capacityRepository->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->first();

        if (!$capacity) {
            return true;
        }

        $usagePercent = ($capacity->used_capacity / $capacity->total_capacity) * 100;
        return $usagePercent < $capacity->threshold_critical;
    }

    protected function updateOrCreateCapacity(string $resourceType, int $resourceId, int $total, int $used, int $available): CapacityManagement
    {
        $capacity = $this->capacityRepository->firstOrNew([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
        ]);

        $capacity->uuid = (string)\Illuminate\Support\Str::uuid();
        $capacity->total_capacity = $total;
        $capacity->used_capacity = $used;
        $capacity->available_capacity = $available;
        $capacity->last_checked_at = now();

        if ($capacity->exists) {
            $capacity->save();
        } else {
            $capacity->save();
        }

        $usagePercent = ($used / $total) * 100;
        if ($usagePercent >= $capacity->threshold_critical) {
            Log::warning('Capacity critical threshold reached', [
                'type' => $resourceType,
                'id' => $resourceId,
            ]);
        } elseif ($usagePercent >= $capacity->threshold_warning) {
            Log::warning('Capacity warning threshold reached', [
                'type' => $resourceType,
                'id' => $resourceId,
            ]);
        }

        return $capacity;
    }
}
