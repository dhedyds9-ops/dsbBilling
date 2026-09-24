<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Installation;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $installation = Installation::findOrFail($id);
        $installation->delete();
        session()->flash('success', 'Installation berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Installation::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $installations = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.installation.index', compact('installations'));
    }
}
