<?php

namespace App\Livewire\ACS;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\ACS\ACSAlarm;
use App\Models\ACS\ProvisionQueue;

class Dashboard extends AdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'dashboard';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS'],
        ];
    }

    public function render()
    {
        $stats = [
            'online_devices' => ACSDevice::online()->count(),
            'offline_devices' => ACSDevice::offline()->count(),
            'pending_tasks' => DeviceTask::where('status', 'pending')->count(),
            'running_tasks' => DeviceTask::where('status', 'running')->count(),
            'failed_tasks' => DeviceTask::where('status', 'failed')->count(),
            'provision_success' => ProvisionQueue::where('status', 'completed')->count(),
            'provision_failed' => ProvisionQueue::where('status', 'failed')->count(),
            'active_alarms' => ACSAlarm::where('status', 'active')->count(),
        ];

        $recentDevices = ACSDevice::latest()->take(5)->get();
        $recentTasks = DeviceTask::latest()->take(5)->get();

        return view('livewire.acs.dashboard', compact('stats', 'recentDevices', 'recentTasks'));
    }
}
