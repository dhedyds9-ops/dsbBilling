<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;

class Edit extends AdminComponent
{
    public $installationId;
    public $customer_name = '';
    public $notes = '';
    public $installation_date = '';
    public $technician = '';
    public $status = 'scheduled';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';
        $this->installationId = $id;
        $installation = Installation::findOrFail($id);
        $this->customer_name = $installation->customer_name;
        $this->notes = $installation->notes;
        $this->installation_date = $installation->installation_date?->format('Y-m-d');
        $this->technician = $installation->technician;
        $this->status = $installation->status;
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

        $installation = Installation::findOrFail($this->installationId);
        $installation->update([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'installation_date' => $this->installation_date,
            'technician' => $this->technician,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Installation berhasil diperbarui!');
        return redirect()->route('crm.installations.index');
    }

    public function render()
    {
        return view('livewire.crm.installation.edit');
    }
}
