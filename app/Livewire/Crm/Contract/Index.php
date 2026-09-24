<?php

namespace App\Livewire\Crm\Contract;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Contract;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'contracts';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        session()->flash('success', 'Contract berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Contract::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $contracts = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.contract.index', compact('contracts'));
    }
}
