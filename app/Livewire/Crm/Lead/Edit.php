<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\AdminComponent;
use App\Models\CRM\Lead;

class Edit extends AdminComponent
{
    public $leadId;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $notes = '';
    public $status = 'new';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
        $this->leadId = $id;
        $lead = Lead::findOrFail($id);
        $this->name = $lead->name;
        $this->email = $lead->email;
        $this->phone = $lead->phone;
        $this->address = $lead->address;
        $this->notes = $lead->notes;
        $this->status = $lead->status;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $lead = Lead::findOrFail($this->leadId);
        $lead->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Lead berhasil diperbarui!');
        return redirect()->route('crm.leads.index');
    }

    public function render()
    {
        return view('livewire.crm.lead.edit');
    }
}
