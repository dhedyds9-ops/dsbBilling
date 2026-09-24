<?php

namespace App\Livewire\Crm\Quotation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Quotation;

class Edit extends AdminComponent
{
    public $quotationId;
    public $customer_name = '';
    public $notes = '';
    public $total = 0;
    public $status = 'draft';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'quotations';
        $this->quotationId = $id;
        $quotation = Quotation::findOrFail($id);
        $this->customer_name = $quotation->customer_name;
        $this->notes = $quotation->notes;
        $this->total = $quotation->total;
        $this->status = $quotation->status;
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected',
        ]);

        $quotation = Quotation::findOrFail($this->quotationId);
        $quotation->update([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'total' => $this->total,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Quotation berhasil diperbarui!');
        return redirect()->route('crm.quotations.index');
    }

    public function render()
    {
        return view('livewire.crm.quotation.edit');
    }
}
