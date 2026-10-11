<?php

namespace App\Livewire\NOC\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Router;
use App\Models\ISP\RouterMonitoringLog;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = 'all';
    public string $sortField    = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 25;

    public function configure(): void
    {
        $this;
    }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'routers';
        $latest = \App\Models\ISP\RouterMonitoringLog::latest()->first();
        if (!$latest || $latest->created_at->lt(now()->subSeconds(90))) {
            try {
                app(\App\Services\Monitoring\Collectors\RouterCollector::class)->collect();
            } catch (\Throwable $e) {}
        }
    }

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

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
    public function routers(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $staleAt = now()->subMinutes(15);

        $query = Router::withoutTrashed()
            ->with([
                'vendor:id,name',
                'pop:id,name',
                'latestMonitoringLog',
            ])
            ->select([
                'id', 'name', 'code', 'model', 'ip_address',
                'status', 'last_seen_at', 'routeros_version',
                'vendor_id', 'pop_id',
            ]);

        if ($this->statusFilter === 'online') {
            $query->where(function ($q) use ($staleAt) {
                $q->whereHas('monitoringLogs', fn ($sub) =>
                    $sub->where('is_online', true)->where('created_at', '>=', $staleAt)
                )->orWhere(function ($sub) use ($staleAt) {
                    $sub->whereIn('status', ['online', 'active'])
                        ->where('last_seen_at', '>=', $staleAt);
                });
            });
        } elseif ($this->statusFilter === 'offline') {
            $query->where(function ($q) use ($staleAt) {
                $q->whereDoesntHave('monitoringLogs', fn ($sub) =>
                    $sub->where('is_online', true)->where('created_at', '>=', $staleAt)
                )->where(function ($sub) use ($staleAt) {
                    $sub->whereNotIn('status', ['online', 'active'])
                        ->orWhereNull('last_seen_at')
                        ->orWhere('last_seen_at', '<', $staleAt);
                });
            });
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%")
                  ->orWhere('hostname', 'like', "%{$this->search}%")
                  ->orWhere('model', 'like', "%{$this->search}%");
            });
        }

        if (in_array($this->sortField, ['name', 'ip_address', 'status'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function summary(): array
    {
        $staleAt = now()->subMinutes(15);
        $total   = Router::withoutTrashed()->count();
        $online  = Router::withoutTrashed()->where(function ($q) use ($staleAt) {
            $q->whereHas('monitoringLogs', fn ($sub) =>
                $sub->where('is_online', true)->where('created_at', '>=', $staleAt)
            )->orWhere(function ($sub) use ($staleAt) {
                $sub->whereIn('status', ['online', 'active'])
                    ->where('last_seen_at', '>=', $staleAt);
            });
        })->count();

        return [
            'total'   => $total,
            'online'  => $online,
            'offline' => max(0, $total - $online),
        ];
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        $this;
        return view('livewire.noc.router.index', [
            'routers' => $this->routers,
            'summary' => $this->summary,
        ]);
    }
}



