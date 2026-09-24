<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\DeviceAssignment;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\Router;
use App\Models\ISP\Switcher;
use App\Models\ISP\AccessPoint;
use App\Models\ISP\NasDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceAssignmentService
{
    public function assignOlt(int $serviceInstanceId, int $oltId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $oltId, $userId) {
            $olt = Olt::findOrFail($oltId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'olt',
                'device_id' => $oltId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function assignOnu(int $serviceInstanceId, int $onuId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $onuId, $userId) {
            $onu = Onu::findOrFail($onuId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'onu',
                'device_id' => $onuId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function assignRouter(int $serviceInstanceId, int $routerId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $routerId, $userId) {
            $router = Router::findOrFail($routerId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'router',
                'device_id' => $routerId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function assignSwitch(int $serviceInstanceId, int $switchId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $switchId, $userId) {
            $switch = Switcher::findOrFail($switchId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'switch',
                'device_id' => $switchId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function assignAccessPoint(int $serviceInstanceId, int $apId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $apId, $userId) {
            $ap = AccessPoint::findOrFail($apId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'access_point',
                'device_id' => $apId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function assignNas(int $serviceInstanceId, int $nasId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($serviceInstanceId, $nasId, $userId) {
            $nas = NasDevice::findOrFail($nasId);

            return DeviceAssignment::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'device_type' => 'nas',
                'device_id' => $nasId,
                'status' => 'active',
                'assigned_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function revokeDevice(int $assignmentId, int $userId): DeviceAssignment
    {
        return DB::transaction(function () use ($assignmentId, $userId) {
            $assignment = DeviceAssignment::findOrFail($assignmentId);
            $assignment->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'updated_by' => $userId,
            ]);

            return $assignment;
        });
    }
}
