<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\VlanAllocation;
use App\Models\Provisioning\IpAllocation;
use App\Models\Provisioning\QueueAllocation;
use App\Models\Provisioning\DeviceAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Domain\Provisioning\Events\VlanAllocatedEvent;
use Src\Domain\Provisioning\Events\IpAllocatedEvent;
use Src\Domain\Provisioning\Events\QueueAllocatedEvent;
use Src\Domain\Provisioning\Events\DeviceAssignedEvent;

class ResourceAllocationService
{
    public function allocateVlan(int $serviceInstanceId, int $vlanId, int $userId): VlanAllocation
    {
        return DB::transaction(function () use ($serviceInstanceId, $vlanId, $userId) {
            $allocation = VlanAllocation::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'vlan_id' => $vlanId,
                'status' => 'active',
                'allocated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            event(new VlanAllocatedEvent(
                $allocation->uuid,
                $serviceInstanceId
            ));

            return $allocation;
        });
    }

    public function allocateIp(int $serviceInstanceId, int $ipPoolId, int $userId): IpAllocation
    {
        return DB::transaction(function () use ($serviceInstanceId, $ipPoolId, $userId) {
            $allocation = IpAllocation::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'ip_pool_id' => $ipPoolId,
                'status' => 'active',
                'allocated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            event(new IpAllocatedEvent(
                $allocation->uuid,
                $serviceInstanceId
            ));

            return $allocation;
        });
    }

    public function allocateQueue(int $serviceInstanceId, int $queueId, int $userId): QueueAllocation
    {
        return DB::transaction(function () use ($serviceInstanceId, $queueId, $userId) {
            $allocation = QueueAllocation::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'queue_id' => $queueId,
                'status' => 'active',
                'allocated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            event(new QueueAllocatedEvent(
                $allocation->uuid,
                $serviceInstanceId
            ));

            return $allocation;
        });
    }

    public function assignDevice(int $serviceInstanceId, string $deviceType, int $deviceId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $deviceType, $deviceId, $userId) {
            $assignment = DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            event(new DeviceAssignedEvent(
                $assignment->uuid,
                $serviceInstanceId
            ));

            return $assignment;
        });
    }
}
