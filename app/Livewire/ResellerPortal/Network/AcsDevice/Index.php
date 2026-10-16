<?php

namespace App\Livewire\ResellerPortal\Network\AcsDevice;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;
use App\Services\GenieAcsService;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $status = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-acs';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network', 'url' => route('reseller-portal.network.map')],
            ['label' => 'GenieACS Devices (TR-069)', 'url' => '#'],
        ];

        if (request()->query('search')) {
            $this->search = request()->query('search');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function rebootDevice($deviceId)
    {
        $device = ACSDevice::forReseller()->findOrFail($deviceId);

        try {
            $genie = app(GenieAcsService::class);
            $genie->rebootDevice($device->serial_number);
            session()->flash('success', "Perintah Reboot untuk modem {$device->serial_number} berhasil dikirim via TR-069!");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal mengirim perintah reboot: " . $e->getMessage());
        }
    }

    public function render()
    {
        $query = ACSDevice::forReseller()
            ->with(['onu', 'customerService.customer'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('serial_number', 'like', '%' . $this->search . '%')
                        ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                        ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                        ->orWhere('model', 'like', '%' . $this->search . '%')
                        ->orWhere('manufacturer', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customerService.customer', function ($c) {
                            $c->withoutGlobalScopes()->where(function($sq) {
                                $sq->where('name', 'like', '%' . $this->search . '%')
                                   ->orWhere('code', 'like', '%' . $this->search . '%');
                            });
                        });
                });
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            });

        $devices = $query->latest('last_inform')->paginate(15);

        return view('livewire.reseller-portal.network.acs-device.index', [
            'devices' => $devices,
        ]);
    }
}
