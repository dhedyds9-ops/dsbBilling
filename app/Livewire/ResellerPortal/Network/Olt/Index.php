<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Services\Auth\ResellerScopeHelper;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data OLT', 'url' => '#'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $olts = Olt::forReseller()
            ->with(['pop', 'vendor', 'branch'])
            ->withCount([
                'onus as onus_total_count' => fn ($q) => $q->forReseller(),
                'onus as onus_online_count' => fn ($q) => $q->forReseller()->where('status', 'active'),
                'onus as onus_offline_count' => fn ($q) => $q->forReseller()->where('status', 'inactive'),
            ])
            ->when(trim($this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('model', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when(
                in_array($this->sortField, ['name', 'code', 'ip_address', 'status'], true),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection),
                fn ($q) => $q->orderBy('name', 'asc')
            )
            ->paginate($this->perPage);

        $totalOlt = Olt::forReseller()->count();
        $activeOlt = Olt::forReseller()->where('status', 'active')->count();
        $totalOnu = Onu::forReseller()->count();
        $onlineOnu = Onu::forReseller()->where('status', 'active')->count();
        $offlineOnu = max(0, $totalOnu - $onlineOnu);
        $onlinePercentage = $totalOnu > 0 ? round(($onlineOnu / $totalOnu) * 100, 1) : 0;

        $stats = [
            'total_olt'         => $totalOlt,
            'active_olt'        => $activeOlt,
            'total_onu'         => $totalOnu,
            'online_onu'        => $onlineOnu,
            'offline_onu'       => $offlineOnu,
            'online_percentage' => $onlinePercentage,
        ];

        return view('livewire.reseller-portal.network.olt.index', [
            'olts'      => $olts,
            'stats'     => $stats,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}
