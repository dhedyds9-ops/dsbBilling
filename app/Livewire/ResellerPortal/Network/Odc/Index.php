<?php

namespace App\Livewire\ResellerPortal\Network\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $odcs = Odc::forReseller()->with('olt', 'branch')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->paginate(15);

        return view('livewire.reseller-portal.network.odc.index', [
            'odcs' => $odcs
        ])->layout('layouts.reseller-portal');
    }
}
