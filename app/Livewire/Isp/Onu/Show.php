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
        $this->onu = OnuModel::with(['olt', 'vendor', 'onuPorts', 'acsDevice', 'customerService.customer', 'customerService.internetPlan', 'odp', 'branch'])->findOrFail($id);
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

    public function refreshLiveOpticalPower()
    {
        try {
            $olt = $this->onu->olt;
            if (!$olt) {
                throw new \Exception("OLT untuk ONU ini tidak terdaftar.");
            }

            // 1. Coba baca redaman dari GenieACS jika ada
            if ($this->onu->acsDevice && !empty($this->onu->acsDevice->signal)) {
                $this->onu->rx_power_dbm = (float)$this->onu->acsDevice->signal;
                $this->onu->last_seen_at = now();
                $this->onu->save();
            }

            // 2. Coba jalankan sinkronisasi port dari OLT driver
            try {
                $driver = app(\App\Services\Adapters\Provisioning\OltRegistry::class)->forOlt($olt);
                $ponPortIdx = (int)($this->onu->pon_port ?: 1);
                $onusOnPort = $driver->getOnuRxPower($ponPortIdx);
                foreach ($onusOnPort as $item) {
                    if (strtoupper($item['serial_number'] ?? '') === strtoupper($this->onu->serial_number)) {
                        if (isset($item['rx_power_dbm']) && $item['rx_power_dbm'] !== null) {
                            $this->onu->rx_power_dbm = (float)$item['rx_power_dbm'];
                        }
                        if (isset($item['tx_power_dbm']) && $item['tx_power_dbm'] !== null) {
                            $this->onu->tx_power_dbm = (float)$item['tx_power_dbm'];
                        }
                        if (isset($item['temperature']) && $item['temperature'] !== null) {
                            $this->onu->temperature = (float)$item['temperature'];
                        }
                        $this->onu->status = ($item['status'] ?? 'offline') === 'online' ? 'active' : $this->onu->status;
                        $this->onu->last_seen_at = now();
                        $this->onu->save();
                        break;
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Live OLT poll error for ONU {$this->onu->serial_number}: " . $e->getMessage());
            }

            $this->onu->refresh();
            $this->dispatch('toast', type: 'success', message: 'Data redaman & status optik berhasil diperbarui!');
        } catch (\Throwable $th) {
            $this->dispatch('toast', type: 'error', message: 'Gagal memperbarui: ' . $th->getMessage());
        }
    }

    public function getRealVendorNameProperty(): string
    {
        $sn = strtoupper(trim((string)$this->onu->serial_number));
        if (str_starts_with($sn, 'ZTEG') || str_starts_with($sn, '5A544547')) return 'ZTE';
        if (str_starts_with($sn, 'HWTC') || str_starts_with($sn, '48575443')) return 'Huawei';
        if (str_starts_with($sn, 'FHTT') || str_starts_with($sn, '46485454')) return 'Fiberhome';
        if (str_starts_with($sn, 'CDTE')) return 'C-Data';
        if (str_starts_with($sn, 'VSOL')) return 'V-SOL';
        return $this->onu->vendor?->name ?? 'ZTE / Universal';
    }

    public function render()
    {
        return view('livewire.isp.onu.show');
    }
}
