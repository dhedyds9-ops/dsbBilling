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
        $this->sortField = 'id';
        $this->sortDirection = 'desc';

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'CRM', 'url' => '#'],
            ['label' => 'Instalasi', 'url' => route('crm.installations.index')],
        ];
    }

    public function delete($id)
    {
        $installation = Installation::findOrFail($id);
        $installation->delete();
        session()->flash('success', 'Data instalasi berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Fitur ekspor data instalasi sedang diproses.');
    }

    public function render()
    {
        $query = Installation::query()->with(['customer', 'contract.customer', 'survey.prospect', 'assignedTo', 'technician']);

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('onu_serial_number', 'like', "%{$s}%")
                  ->orWhere('router_serial_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
                  ->orWhereHas('contract', function($cq) use ($s) {
                      $cq->where('customer_name', 'like', "%{$s}%")
                         ->orWhere('contract_number', 'like', "%{$s}%")
                         ->orWhereHas('customer', fn($ccq) => $ccq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
                  })
                  ->orWhereHas('survey.prospect', function($pq) use ($s) {
                      $pq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('assignedTo', fn($aq) => $aq->where('name', 'like', "%{$s}%"));
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        $installations = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.installation.index', compact('installations'));
    }
}
