<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;

class Create extends AdminComponent
{
    public $customer_name = '';
    public $notes = '';
    public $installation_date = '';
    public $technician = '';
    public $status = 'scheduled';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';
        $this->installation_date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'installation_date' => 'required|date',
            'technician' => 'nullable|string|max:255',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        Installation::create([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'installation_date' => $this->installation_date,
            'technician' => $this->technician,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Installation berhasil dibuat!');
        return redirect()->route('crm.installations.index');
    }

    public function render()
    {
        return view('livewire.crm.installation.create');
    }
}
