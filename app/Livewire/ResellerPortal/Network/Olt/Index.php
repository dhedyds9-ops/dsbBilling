<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
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
        $olts = Olt::forReseller()->with('pop', 'branch')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            })
            ->paginate(15);

        return view('livewire.reseller-portal.network.olt.index', [
            'olts' => $olts
        ]);
    }
}

