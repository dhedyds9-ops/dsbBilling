<?php

namespace App\Livewire\Crm\Contract;

use App\Livewire\AdminComponent;
use App\Models\CRM\Contract;

class Create extends AdminComponent
{
    public $customer_name = '';
    public $notes = '';
    public $start_date = '';
    public $end_date = '';
    public $status = 'draft';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'contracts';
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addYear()->format('Y-m-d');
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

        Contract::create([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Contract berhasil dibuat!');
        return redirect()->route('crm.contracts.index');
    }

    public function render()
    {
        return view('livewire.crm.contract.create');
    }
}
