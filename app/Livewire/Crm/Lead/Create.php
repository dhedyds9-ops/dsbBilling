<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\AdminComponent;
use App\Models\CRM\Lead;

class Create extends AdminComponent
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $notes = '';
    public $status = 'new';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
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

        Lead::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Lead berhasil dibuat!');
        return redirect()->route('crm.leads.index');
    }

    public function render()
    {
        return view('livewire.crm.lead.create');
    }
}
