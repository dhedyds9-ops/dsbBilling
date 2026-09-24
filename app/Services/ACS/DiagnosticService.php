<?php

namespace App\Services\ACS;

use App\Models\ACS\DeviceTask;

class DiagnosticService
{
    public function runPing(int $deviceId, string $host)
    {
        return $this->createTask($deviceId, 'ping', ['host' => $host]);
    }

    public function runTraceroute(int $deviceId, string $host)
    {
        return $this->createTask($deviceId, 'traceroute', ['host' => $host]);
    }

    public function runSpeedTest(int $deviceId)
    {
        return $this->createTask($deviceId, 'speed_test', []);
    }

    public function getWifiScan(int $deviceId)
    {
        return $this->createTask($deviceId, 'wifi_scan', []);
    }

    protected function createTask(int $deviceId, string $type, array $parameters)
    {
        return DeviceTask::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'acs_device_id' => $deviceId,
            'type' => $type,
            'parameters' => $parameters,
            'status' => 'pending',
            'created_by' => auth()->id() ?? null,
            'updated_by' => auth()->id() ?? null,
        ]);
    }
}
