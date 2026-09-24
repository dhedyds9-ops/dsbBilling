<?php

namespace App\Livewire\NOC\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuSignal;
use App\Models\Alarm;
use App\Services\ISP\NetworkTopologyService;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public int $onuId;
    public string $activeTab = 'overview';

    public function configure(): void
    {
        }

    public function mount($onu = null): void
    {
        parent::mount();
        $this->onuId        = (int) $onu;
        $this->activeModule = 'noc';
        $this->activePage   = 'onus';
    }

    public function setTab(string $tab): void
    {
        $allowed = ['overview', 'optical', 'service', 'provisioning', 'alarms'];
        if (in_array($tab, $allowed)) {
            $this->activeTab = $tab;
        }
    }

    #[Computed]
    public function onu(): Onu
    {
        return Onu::withoutTrashed()
            ->with([
                'olt:id,name,ip_address,status',
                'ponPort:id,name,port_number',
                'odp:id,name,code',
                'vendor:id,name',
                'customerService:id,onu_id,customer_id,service_type,status',
                'customerService.customer:id,name,code',
                // Explicitly no wifi_password, admin_password (in $hidden)
            ])
            ->select([
                'id', 'name', 'code', 'serial_number', 'mac_address',
                'model', 'firmware_version', 'hardware_version',
                'status', 'provision_status',
                'rx_power_dbm', 'tx_power_dbm', 'snr_db', 'temperature',
                'last_seen_at', 'provisioned_at',
                'olt_id', 'pon_port_id', 'odp_id', 'vendor_id',
                'pon_port', 'onu_id_on_olt', 'profile_name',
                'wifi_ssid',
                // wifi_password and admin_password hidden by $hidden
            ])
            ->findOrFail($this->onuId);
    }

    #[Computed]
    public function signalHistory(): \Illuminate\Support\Collection
    {
        return OnuSignal::where('onu_id', $this->onuId)
            ->select(['id', 'rx_power_dbm', 'tx_power_dbm', 'snr_db', 'temperature', 'status', 'measured_at'])
            ->orderBy('measured_at', 'desc')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function activeAlarms(): \Illuminate\Database\Eloquent\Collection
    {
        $orderBy = \App\Services\Support\DbCompat::fieldOrder('level', ['critical', 'warning', 'info']);
        return Alarm::where('status', 'open')
            ->where('source_type', Onu::class)
            ->where('source_id', $this->onuId)
            ->orderByRaw($orderBy)
            ->orderBy('started_at', 'desc')
            ->get(['id', 'level', 'title', 'description', 'started_at', 'status']);
    }

    #[Computed]
    public function networkPath(): array
    {
        return app(NetworkTopologyService::class)->getCustomerPath($this->onu);
    }

    public function getOnuStatus(): string
    {
        $onu = $this->onu;
        if ($onu->status === 'los' || ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -30)) return 'LOS';
        if (!$onu->last_seen_at || $onu->last_seen_at->diffInMinutes(now()) > 5) return 'OFFLINE';
        if ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) return 'LOW_RX';
        return 'ONLINE';
    }

    public function getRxClass(): string
    {
        $rx = $this->onu->rx_power_dbm;
        if ($rx === null) return 'text-gray-500';
        if ($rx < -30) return 'text-red-400';
        if ($rx < -27) return 'text-amber-400';
        if ($rx >= -20 && $rx <= -10) return 'text-emerald-400';
        return 'text-emerald-400';
    }

    public function syncTR069(): void
    {
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            $driver->getDeviceParameters($this->onu->serial_number);
            $this->dispatch('toast', type: 'success', message: 'TR-069 Parameters synced from GenieACS.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Sync failed: ' . $e->getMessage());
        }
    }

    public function rebootOnu(): void
    {
        if (!auth()->user()?->hasPermission(\App\Enums\UserPermission::NocOnuManage->value)) {
            $this->dispatch('toast', type: 'error', message: 'Unauthorized.');
            return;
        }
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            $driver->rebootDevice($this->onu->serial_number);
            $this->dispatch('toast', type: 'success', message: 'Reboot command sent via TR-069.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Reboot failed: ' . $e->getMessage());
        }
    }
    
    public function factoryResetOnu(): void
    {
        if (!auth()->user()?->hasPermission(\App\Enums\UserPermission::NocOnuManage->value)) {
            $this->dispatch('toast', type: 'error', message: 'Unauthorized.');
            return;
        }
        try {
            $driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
            $driver->factoryResetDevice($this->onu->serial_number);
            $this->dispatch('toast', type: 'success', message: 'Factory reset command sent via TR-069.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Reset failed: ' . $e->getMessage());
        }
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        $user = auth()->user();
        $canManageOnu = $user ? $user->hasPermission(\App\Enums\UserPermission::NocOnuManage->value) : false;

        return view('livewire.noc.onu.show', [
            'onu'        => $this->onu,
            'signals'    => $this->activeTab === 'optical' ? $this->signalHistory : collect(),
            'alarms'     => $this->activeAlarms,
            'path'       => $this->networkPath,
            'status'     => $this->getOnuStatus(),
            'rxClass'    => $this->getRxClass(),
            'canManage'  => $canManageOnu,
        ]);
    }
}



