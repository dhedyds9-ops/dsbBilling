<?php

namespace App\Livewire\NOC\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    #[\Livewire\Attributes\Url]
    public string $search = '';
    
    #[\Livewire\Attributes\Url]
    public string $statusFilter = 'all';
    
    #[\Livewire\Attributes\Url]
    public string $oltFilter   = '';
    
    #[\Livewire\Attributes\Url]
    public string $ponFilter   = '';
    public string $sortField   = 'serial_number';
    public string $sortDirection = 'asc';
    #[\Livewire\Attributes\Url]
    public int $perPage = 25;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'onus';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOltFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function onus(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $staleAt  = now()->subMinutes(5);
        $rxLow    = -27.0;
        $rxCrit   = -30.0;

        return Onu::withoutTrashed()
            ->with([
                'olt:id,name',
                'odp:id,name,code',
                'ponPort:id,name,port_number',
                'customerService:id,onu_id,customer_id',
                'customerService.customer:id,name,code',
            ])
            ->select([
                'id', 'name', 'serial_number', 'mac_address', 'status',
                'rx_power_dbm', 'tx_power_dbm', 'temperature',
                'last_seen_at', 'olt_id', 'pon_port_id', 'odp_id',
                'pon_port',
            ])
            ->when($this->oltFilter, fn ($q) => $q->where('olt_id', $this->oltFilter))
            ->when($this->ponFilter, fn ($q) => $q->where('pon_port_id', $this->ponFilter))
            ->when($this->statusFilter === 'online', fn ($q) => $q->where('last_seen_at', '>=', $staleAt))
            ->when($this->statusFilter === 'offline', fn ($q) => $q->where(function ($q) use ($staleAt) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $staleAt);
            }))
            ->when($this->statusFilter === 'los', fn ($q) => $q->where(function ($q) use ($rxCrit) {
                $q->where('status', 'los')->orWhere('rx_power_dbm', '<', $rxCrit);
            }))
            ->when($this->statusFilter === 'low_rx', fn ($q) => $q->whereBetween('rx_power_dbm', [$rxCrit, $rxLow]))
            ->when(!empty($this->search), function ($q) {
                $q->where(function ($sq) {
                    $sq->where('serial_number', 'like', "%{$this->search}%")
                       ->orWhere('name', 'like', "%{$this->search}%")
                       ->orWhere('mac_address', 'like', "%{$this->search}%")
                       ->orWhereHas('customerService.customer', function ($cq) {
                           $cq->where('name', 'like', "%{$this->search}%")
                              ->orWhere('code', 'like', "%{$this->search}%");
                       });
                });
            })
            ->when(
                in_array($this->sortField, ['serial_number', 'status', 'rx_power_dbm', 'last_seen_at', 'name']),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->paginate($this->perPage === 0 ? 1000000 : $this->perPage);
    }

    #[Computed]
    public function olts(): \Illuminate\Database\Eloquent\Collection
    {
        return Olt::withoutTrashed()->select('id', 'name')->orderBy('name')->get();
    }

    #[Computed]
    public function summary(): array
    {
        $staleAt = now()->subMinutes(5);
        return [
            'total'   => Onu::withoutTrashed()->count(),
            'online'  => Onu::withoutTrashed()->where('last_seen_at', '>=', $staleAt)->count(),
            'offline' => Onu::withoutTrashed()->where(function ($q) use ($staleAt) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $staleAt);
            })->count(),
            'los'     => Onu::withoutTrashed()->where(function ($q) {
                $q->where('status', 'los')->orWhere('rx_power_dbm', '<', -30.0);
            })->count(),
            'low_rx'  => Onu::withoutTrashed()->whereBetween('rx_power_dbm', [-30.0, -27.0])->count(),
        ];
    }

    public function getOnuStatus(Onu $onu): string
    {
        if ($onu->status === 'los' || ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -30)) return 'LOS';
        if (!$onu->last_seen_at || $onu->last_seen_at->diffInMinutes(now()) > 5) return 'OFFLINE';
        if ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) return 'LOW_RX';
        return 'ONLINE';
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.onu.index', [
            'onus'    => $this->onus,
            'olts'    => $this->olts,
            'summary' => $this->summary,
        ]);
    }
}





