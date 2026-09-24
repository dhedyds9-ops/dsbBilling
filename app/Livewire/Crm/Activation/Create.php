<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Activation;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $customer_name = '';
    public $notes = '';
    public $activation_date = '';
    public $service_package = '';
    public $status = 'pending';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'activations';
        $this->activation_date = now()->format('Y-m-d');
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

        Activation::create([
            'uuid' => (string) Str::uuid(),
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'activation_date' => $this->activation_date,
            'service_package' => $this->service_package,
            'status' => $this->status,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Activation berhasil dibuat!');
        return redirect()->route('crm.activations.index');
    }

    public function render()
    {
        return view('livewire.crm.activation.create');
    }
}
