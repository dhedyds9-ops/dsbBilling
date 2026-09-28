<?php

namespace App\Livewire\ResellerPortal\Network\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp;
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
        $odps = Odp::forReseller()->with('odc', 'branch')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->paginate(15);

        return view('livewire.reseller-portal.network.odp.index', [
            'odps' => $odps
        ])->layout('layouts.reseller-portal');
    }
}
