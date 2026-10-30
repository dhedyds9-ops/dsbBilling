<?php

namespace App\Livewire\ResellerPortal\Network\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $status = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-onus';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data ONU / Modem', 'url' => '#'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Onu::forReseller()
            ->with(['olt', 'odp', 'customerService.customer'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                        ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customerService.customer', function ($c) {
                            $c->withoutGlobalScopes()->where(function($sq) {
                                $sq->where('name', 'like', '%' . $this->search . '%')
                                   ->orWhere('code', 'like', '%' . $this->search . '%');
                            });
                        });
                });
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            });

        $onus = $query->latest('id')->paginate(15);

        return view('livewire.reseller-portal.network.onu.index', [
            'onus' => $onus,
            'territory' => \App\Services\Auth\ResellerScopeHelper::describeTerritory(),
        ]);
    }
}
