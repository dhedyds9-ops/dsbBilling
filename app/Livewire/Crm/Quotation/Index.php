<?php

namespace App\Livewire\Crm\Quotation;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Quotation;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'quotations';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();
        session()->flash('success', 'Quotation berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Quotation::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $quotations = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.quotation.index', compact('quotations'));
    }
}
