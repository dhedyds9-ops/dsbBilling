<?php

namespace App\Livewire\NOC\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\PonPort;
use App\Models\ISP\Onu;
use App\Models\Alarm;
use App\Services\NOC\ImpactAnalysisService;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public int $oltId;
    public string $activeTab = 'overview';

    // Polling: 60 seconds for device detail
    // Defined in view via wire:poll.60000ms

    public function configure(): void
    {
        }

    public function mount($olt = null): void
    {
        parent::mount();
        $this->oltId        = (int) $olt;
        $this->activeModule = 'noc';
        $this->activePage   = 'olts';
    }

    public function setTab(string $tab): void
    {
        $allowed = ['overview', 'pon', 'onus', 'traffic', 'alarms', 'events'];
        if (in_array($tab, $allowed)) {
            $this->activeTab = $tab;
        }
    }

    #[Computed]
    public function olt(): Olt
    {
        return Olt::withoutTrashed()
            ->with(['vendor:id,name', 'pop:id,name'])
            ->withCount(['onus', 'ponPorts'])
            ->select([
                'id', 'name', 'code', 'model', 'ip_address',
                'status', 'last_polled_at', 'temperature',
                'onu_active_count', 'pon_port_count', 'uptime_text',
                'firmware_version', 'vendor_id', 'pop_id',
                'snmp_version', 'snmp_port',
                // Hidden: password, snmp_community_write — never returned by model due to $hidden
            ])
            ->findOrFail($this->oltId);
    }

    #[Computed]
    public function ponPorts(): \Illuminate\Database\Eloquent\Collection
    {
        return PonPort::where('olt_id', $this->oltId)
            ->withCount(['onus', 'onus as active_onu_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('port_number')
            ->get(['id', 'name', 'code', 'port_number', 'type', 'status']);
    }

    #[Computed]
    public function recentOnus(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Onu::where('olt_id', $this->oltId)
            ->with(['odp:id,name', 'ponPort:id,name'])
            ->select([
                'id', 'name', 'serial_number', 'status',
                'rx_power_dbm', 'tx_power_dbm', 'temperature',
                'last_seen_at', 'olt_id', 'pon_port_id', 'odp_id',
            ])
            ->orderBy('status')
            ->paginate(20);
    }

    #[Computed]
    public function activeAlarms(): \Illuminate\Database\Eloquent\Collection
    {
        return Alarm::where('status', 'open')
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('source_type', Olt::class)
                       ->where('source_id', $this->oltId);
                })->orWhere(function ($sq) {
                    // Also alarms on ONU belonging to this OLT
                    $onuIds = Onu::where('olt_id', $this->oltId)->pluck('id');
                    $sq->where('source_type', Onu::class)
                       ->whereIn('source_id', $onuIds);
                });
            })
            ->orderByRaw(\App\Services\Support\DbCompat::fieldOrder('level', ['critical', 'warning', 'info']))
            ->orderBy('started_at', 'desc')
            ->limit(50)
            ->get(['id', 'level', 'title', 'source_name', 'started_at', 'status', 'description']);
    }

    #[Computed]
    public function impact(): array
    {
        return app(ImpactAnalysisService::class)->analyzeOltImpact($this->oltId);
    }

    public function getOltStatus(): string
    {
        $olt = $this->olt;
        if ($olt->status !== 'active') return 'OFFLINE';
        if (!$olt->last_polled_at) return 'UNKNOWN';
        if ($olt->last_polled_at->diffInMinutes(now()) > 15) return 'OFFLINE';
        if ($olt->temperature && $olt->temperature > 60) return 'WARNING';
        return 'ONLINE';
    }

    public function getOnuStatus(Onu $onu): string
    {
        if ($onu->status === 'los' || ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -30)) return 'LOS';
        if (!$onu->last_seen_at || $onu->last_seen_at->diffInMinutes(now()) > 5) return 'OFFLINE';
        if ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) return 'LOW_RX';
        return 'ONLINE';
    }

    public function syncOlt(): void
    {
        try {
            $result = app(\App\Services\ISP\OltPollingService::class)->pollOlt($this->olt);
            if ($result['success']) {
                $this->dispatch('toast', type: 'success', message: 'OLT data synced successfully.');
            } else {
                $this->dispatch('toast', type: 'error', message: 'Sync failed: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.olt.show', [
            'olt'        => $this->olt,
            'ponPorts'   => $this->activeTab === 'pon' ? $this->ponPorts : collect(),
            'onus'       => $this->activeTab === 'onus' ? $this->recentOnus : null,
            'alarms'     => $this->activeTab === 'alarms' ? $this->activeAlarms : collect(),
            'impact'     => $this->impact,
            'status'     => $this->getOltStatus(),
        ]);
    }
}



