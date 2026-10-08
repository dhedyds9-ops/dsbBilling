<?php

namespace App\Livewire\Isp\Technician\Odp;

use App\Models\ISP\Odp;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.technician-app')]
class Search extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $availability = ''; // 'available', 'full'

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedAvailability()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Odp::with(['olt', 'branch'])
            ->when($this->search, function ($q) {
                $q->where(function($sub) {
                    $sub->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('address', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->when($this->availability === 'available', function ($q) {
                $q->whereRaw('(port_count - used_port_count - reserved_port_count) > 0');
            })
            ->when($this->availability === 'full', function ($q) {
                $q->whereRaw('(port_count - used_port_count - reserved_port_count) <= 0');
            })
            ->latest('id');

        return view('livewire.isp.technician.odp.search', [
            'odps' => $query->paginate(12)
        ])->layout('layouts.noc', ['slot' => '']);
    }
}

