<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Edit extends AdminComponent
{
    public $customerId;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $status = 'active';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $customer = Customer::findOrFail($id);
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->status = $customer->status;
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

        $customer = Customer::findOrFail($this->customerId);
        $customer->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Customer berhasil diperbarui!');
        return redirect()->route('crm.customers.index');
    }

    public function render()
    {
        return view('livewire.crm.customer.edit');
    }
}
