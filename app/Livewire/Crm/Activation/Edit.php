<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Activation;

class Edit extends AdminComponent
{
    public $activationId;
    public $customer_name = '';
    public $notes = '';
    public $activation_date = '';
    public $service_package = '';
    public $status = 'pending';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'activations';
        $this->activationId = $id;
        $activation = Activation::findOrFail($id);
        $this->customer_name = $activation->customer_name;
        $this->notes = $activation->notes;
        $this->activation_date = $activation->activation_date?->format('Y-m-d');
        $this->service_package = $activation->service_package;
        $this->status = $activation->status;
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'activation_date' => 'required|date',
            'service_package' => 'nullable|string|max:255',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $activation = Activation::findOrFail($this->activationId);
        $activation->update([
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'activation_date' => $this->activation_date,
            'service_package' => $this->service_package,
            'status' => $this->status,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Activation berhasil diperbarui!');
        return redirect()->route('crm.activations.index');
    }

    public function render()
    {
        return view('livewire.crm.activation.edit');
    }
}
