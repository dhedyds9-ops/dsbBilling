<?php

namespace App\Livewire\Crm\Quotation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Quotation;

class Create extends AdminComponent
{
    public $customer_name = '';
    public $notes = '';
    public $total = 0;
    public $status = 'draft';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'quotations';
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected',
        ]);

        Quotation::create([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'total' => $this->total,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Quotation berhasil dibuat!');
        return redirect()->route('crm.quotations.index');
    }

    public function render()
    {
        return view('livewire.crm.quotation.create');
    }
}
