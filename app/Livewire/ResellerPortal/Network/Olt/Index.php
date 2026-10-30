<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
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
            ['label' => 'Data OLT', 'url' => '#'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $olts = Olt::forReseller()
            ->with(['pop', 'branch'])
            ->when(trim((string) $this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('model', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.reseller-portal.network.olt.index', [
            'olts' => $olts,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}

