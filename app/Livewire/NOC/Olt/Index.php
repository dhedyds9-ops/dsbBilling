<?php

namespace App\Livewire\NOC\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Services\NOC\ImpactAnalysisService;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 25;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'olts';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
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
    public function olts(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $staleAt = now()->subMinutes(15);

        $query = Olt::withoutTrashed()
            ->with(['vendor:id,name', 'pop:id,name'])
            ->withCount(['onus', 'ponPorts'])
            ->select([
                'id', 'name', 'code', 'model', 'ip_address',
                'status', 'last_polled_at', 'temperature',
                'onu_active_count', 'pon_port_count', 'uptime_text',
                'vendor_id', 'pop_id',
            ]);

        // Status filter
        if ($this->statusFilter === 'online') {
            $query->where('status', 'active')
                  ->where('last_polled_at', '>=', $staleAt);
        } elseif ($this->statusFilter === 'offline') {
            $query->where(function ($q) use ($staleAt) {
                $q->where('status', '!=', 'active')
                  ->orWhereNull('last_polled_at')
                  ->orWhere('last_polled_at', '<', $staleAt);
            });
        } elseif ($this->statusFilter === 'warning') {
            $query->where('status', 'active')
                  ->where('temperature', '>', 60);
        }

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%")
                  ->orWhere('model', 'like', "%{$this->search}%");
            });
        }

        // Sort
        $allowedSorts = ['name', 'ip_address', 'status', 'last_polled_at', 'temperature', 'onu_active_count'];
        if (in_array($this->sortField, $allowedSorts)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function summary(): array
    {
        $staleAt = now()->subMinutes(15);
        return [
            'total'   => Olt::withoutTrashed()->count(),
            'online'  => Olt::withoutTrashed()->where('status', 'active')->where('last_polled_at', '>=', $staleAt)->count(),
            'offline' => Olt::withoutTrashed()->where(function ($q) use ($staleAt) {
                $q->where('status', '!=', 'active')->orWhereNull('last_polled_at')->orWhere('last_polled_at', '<', $staleAt);
            })->count(),
            'warning' => Olt::withoutTrashed()->where('status', 'active')->where('temperature', '>', 60)->count(),
        ];
    }

    public function getOltStatus(Olt $olt): string
    {
        if ($olt->status !== 'active') return 'OFFLINE';
        if (!$olt->last_polled_at) return 'UNKNOWN';
        if ($olt->last_polled_at->diffInMinutes(now()) > 15) return 'OFFLINE';
        if ($olt->temperature && $olt->temperature > 60) return 'WARNING';
        return 'ONLINE';
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.olt.index', [
            'olts'    => $this->olts,
            'summary' => $this->summary,
        ]);
    }
}



