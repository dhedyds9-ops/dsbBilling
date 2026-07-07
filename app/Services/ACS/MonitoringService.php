<?php

namespace App\Services\ACS;

use App\Repositories\MonitoringRepository;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\ACS\ACSAlarm;
use App\Models\ACS\ProvisionQueue;

class MonitoringService
{
    protected $monitoringRepository;

    public function __construct(MonitoringRepository $monitoringRepository)
    {
        $this->monitoringRepository = $monitoringRepository;
    }

    public function getDashboardStats()
    {
        return [
            'online_devices' => ACSDevice::online()->count(),
            'offline_devices' => ACSDevice::offline()->count(),
            'pending_tasks' => DeviceTask::where('status', 'pending')->count(),
            'running_tasks' => DeviceTask::where('status', 'running')->count(),
            'failed_tasks' => DeviceTask::where('status', 'failed')->count(),
            'completed_tasks' => DeviceTask::where('status', 'completed')->count(),
            'active_alarms' => ACSAlarm::where('status', 'active')->count(),
            'provision_success' => ProvisionQueue::where('status', 'completed')->count(),
            'provision_failed' => ProvisionQueue::where('status', 'failed')->count(),
        ];
    }

    public function getRecentDevices()
    {
        return ACSDevice::latest()->take(10)->get();
    }

    public function getRecentTasks()
    {
        return DeviceTask::latest()->take(10)->get();
    }

    public function getRecentAlarms()
    {
        return ACSAlarm::latest()->take(10)->get();
    }
}
