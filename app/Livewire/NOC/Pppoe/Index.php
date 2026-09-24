<?php

namespace App\Livewire\NOC\Pppoe;

use App\Livewire\AdminComponent;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\Router;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $activeTab    = 'pppoe';
    public string $search       = '';
    public string $routerFilter = '';
    public string $sortField    = 'session_started_at';
    public string $sortDirection = 'desc';
    public int $perPage         = 25;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'pppoe';
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRouterFilter(): void { $this->resetPage(); }
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
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
    public function sessions(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = OnlineSession::where('protocol', $this->activeTab)
            ->with(['router:id,name', 'customerService.customer:id,name,code'])
            ->select([
                'id', 'username', 'router_id', 'address', 'caller_id',
                'uptime', 'rate_up', 'rate_down', 'session_started_at', 'last_seen_at',
                'customer_service_id', 'server'
            ]);

        if (!empty($this->routerFilter)) {
            $query->where('router_id', $this->routerFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('username', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%")
                  ->orWhere('caller_id', 'like', "%{$this->search}%")
                  ->orWhereHas('customerService.customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('code', 'like', "%{$this->search}%");
                  });
            });
        }

        $allowedSorts = ['username', 'address', 'session_started_at', 'last_seen_at'];
        if (in_array($this->sortField, $allowedSorts)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function routers(): \Illuminate\Database\Eloquent\Collection
    {
        return Router::withoutTrashed()->select('id', 'name')->orderBy('name')->get();
    }

    #[Computed]
    public function summary(): array
    {
        $total = OnlineSession::where('protocol', $this->activeTab)->count();
        $traffic = \Illuminate\Support\Facades\DB::table('online_sessions')
            ->where('protocol', $this->activeTab)
            ->selectRaw('SUM(rate_up) as rx, SUM(rate_down) as tx')
            ->first();

        return [
            'total' => $total,
            'rx'    => $traffic->rx ?? 0,
            'tx'    => $traffic->tx ?? 0,
        ];
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.pppoe.index', [
            'sessions' => $this->sessions,
            'routers'  => $this->routers,
            'summary'  => $this->summary,
        ]);
    }
}



