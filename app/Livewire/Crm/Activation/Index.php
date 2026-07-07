<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Activation;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'activations';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $activation = Activation::findOrFail($id);
        $activation->delete();
        session()->flash('success', 'Activation berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Activation::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $activations = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.activation.index', compact('activations'));
    }
}
