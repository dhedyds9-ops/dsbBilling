<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        Customer::create([
            'code' => 'CUST-' . strtoupper(Str::random(8)),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Customer berhasil dibuat!');
        return redirect()->route('crm.customers.index');
    }

    public function render()
    {
        return view('livewire.crm.customer.create');
    }
}
