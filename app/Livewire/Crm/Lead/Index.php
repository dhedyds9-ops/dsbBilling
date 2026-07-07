<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Lead;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        session()->flash('success', 'Lead berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Lead::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $leads = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.lead.index', compact('leads'));
    }
}
