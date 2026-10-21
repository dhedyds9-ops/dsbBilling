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
        $query = Installation::query()->with(['contract.customer', 'survey.prospect', 'assignedTo', 'onu']);

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function($q) use ($s) {
                $q->where('onu_serial_number', 'like', "%{$s}%")
                  ->orWhere('router_serial_number', 'like', "%{$s}%")
                  ->orWhereHas('contract', function($cq) use ($s) {
                      $cq->where('customer_name', 'like', "%{$s}%")
                         ->orWhere('contract_number', 'like', "%{$s}%")
                         ->orWhereHas('customer', fn($ccq) => $ccq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
                  })
                  ->orWhereHas('survey.prospect', function($pq) use ($s) {
                      $pq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
                  });
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
