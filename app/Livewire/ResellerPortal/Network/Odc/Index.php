<?php

namespace App\Livewire\ResellerPortal\Network\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc;
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
            ['label' => 'Data ODC', 'url' => '#'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $odcs = Odc::forReseller()
            ->with(['olt', 'branch', 'pop'])
            ->when(trim((string) $this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.reseller-portal.network.odc.index', [
            'odcs' => $odcs,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}

