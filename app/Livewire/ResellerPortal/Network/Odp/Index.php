<?php

namespace App\Livewire\ResellerPortal\Network\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp;
use App\Services\Auth\ResellerScopeHelper;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data ODP', 'url' => '#'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $odps = Odp::forReseller()
            ->with(['odc', 'olt', 'branch'])
            ->when(trim((string) $this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('district', 'like', $like)
                        ->orWhere('village', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.reseller-portal.network.odp.index', [
            'odps' => $odps,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}

