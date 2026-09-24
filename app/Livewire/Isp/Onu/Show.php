<?php

namespace App\Livewire\Isp\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu as OnuModel;
use App\Services\Provisioning\WifiConfigurationService;
use App\Services\Provisioning\WanConfigurationService;
use App\Services\Provisioning\OnuConfigurationJobEngine;
use App\Models\Customer\CustomerService;

class Show extends AdminComponent
{
    public $onuId;
    public $onu;
    // UI State
    public $showWifiModal = false;
    public $showWanModal = false;
    public $showRebootModal = false;
    

    // WiFi Form
    public $wifiSsid = '';
    public $wifiPassword = '';
    public $wifiEnabled = true;

    // WAN Form
    public $wanMode = 'pppoe';
    public $wanUsername = '';
    public $wanPassword = '';
    public $wanVlan = '';
    public $customerServiceId = null;
    public $customerServices = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'onus';
        $this->onuId = $id;
        $this->onu = OnuModel::with(['olt', 'vendor', 'onuPorts'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.onus.index')],
            ['label' => 'ONU', 'url' => route('isp.onus.index')],
            ['label' => $this->onu->name],
        ];
        $this->customerServices = CustomerService::with('networkProfile')->where('onu_id', $id)->get();
        if ($this->customerServices->count() > 0) {
            $cs = $this->customerServices->first();
            $this->customerServiceId = $cs->id;
            $this->wanUsername = $cs->username;
            $this->wanVlan = $cs->networkProfile?->vlan_id;
        }
    }

    
    public function openWifiModal() {
        $this->showWifiModal = true;
    }

    public function closeWifiModal() {
        $this->showWifiModal = false;
    }

    public function configureWifi(WifiConfigurationService $wifiService) {
        $this->authorize('update', $this->onu);
        
        $this->validate([
            'wifiSsid' => 'required|string',
            'wifiPassword' => 'nullable|string',
        ]);
        
        try {
            $wifiService->configureWifi($this->onu, [
                '1' => [
                    'ssid' => $this->wifiSsid,
                    'password' => $this->wifiPassword,
                    'enable' => $this->wifiEnabled
                ]
            ], $this->customerServiceId, auth()->id());
            
            session()->flash('success', 'WiFi Configuration Job Dispatched!');
            $this->closeWifiModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function openWanModal() {
        $this->showWanModal = true;
    }

    public function closeWanModal() {
        $this->showWanModal = false;
    }

    public function configureWan(WanConfigurationService $wanService) {
        $this->authorize('update', $this->onu);

        $this->validate([
            'wanMode' => 'required|string',
            'wanVlan' => 'nullable|integer'
        ]);
        
        try {
            $wanService->configureWan($this->onu, 1, $this->wanMode, [
                'vlan' => $this->wanVlan,
                'username' => $this->wanUsername,
                'password' => $this->wanPassword ?: null
            ], $this->customerServiceId, auth()->id());
            
            session()->flash('success', 'WAN Configuration Job Dispatched!');
            $this->closeWanModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function rebootOnu(OnuConfigurationJobEngine $jobEngine) {
        $this->authorize('update', $this->onu);

        try {
            $jobEngine->dispatchProvisioningJob($this->onu, ['action' => 'reboot'], 'REBOOT', null, auth()->id());
            session()->flash('success', 'Reboot Job Dispatched!');
            $this->showRebootModal = false;
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function verifyConfiguration(OnuConfigurationJobEngine $jobEngine) {
        $this->authorize('update', $this->onu);

        try {
            $jobEngine->dispatchProvisioningJob($this->onu, $this->onu->onuState?->desired_state ?? [], 'VERIFY', null, auth()->id());
            session()->flash('success', 'Verify Job Dispatched!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function reconcileConfiguration(OnuConfigurationJobEngine $jobEngine) {
        $this->authorize('update', $this->onu);

        try {
            $desiredState = $this->onu->onuState?->desired_state;
            if (!$desiredState) {
                throw new \Exception("No desired state exists to reconcile.");
            }
            $jobEngine->dispatchProvisioningJob($this->onu, $desiredState, 'RECONCILE', null, auth()->id());
            session()->flash('success', 'Reconcile Job Dispatched!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.isp.onu.show');
    }
}
