<?php

namespace App\Livewire\Crm\Contract;

use App\Livewire\AdminComponent;
use App\Models\CRM\Contract;

class Edit extends AdminComponent
{
    public $contractId;
    public $customer_name = '';
    public $notes = '';
    public $start_date = '';
    public $end_date = '';
    public $status = 'draft';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'contracts';
        $this->contractId = $id;
        $contract = Contract::findOrFail($id);
        $this->customer_name = $contract->customer_name;
        $this->notes = $contract->notes;
        $this->start_date = $contract->start_date?->format('Y-m-d');
        $this->end_date = $contract->end_date?->format('Y-m-d');
        $this->status = $contract->status;
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,expired,cancelled',
        ]);

        $contract = Contract::findOrFail($this->contractId);
        $contract->update([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Contract berhasil diperbarui!');
        return redirect()->route('crm.contracts.index');
    }

    public function render()
    {
        return view('livewire.crm.contract.edit');
    }
}
