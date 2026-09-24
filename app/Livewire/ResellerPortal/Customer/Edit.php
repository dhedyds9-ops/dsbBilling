<?php
namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Edit extends AdminComponent
{
    public $customerId;
    public $name;
    public $phone;
    public $email;
    public $address;
    public $latitude;
    public $longitude;
    public $notes;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        
        $ownerId = auth()->id();
        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->latitude = $customer->latitude;
        $this->longitude = $customer->longitude;
        $this->notes = $customer->notes;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => route('reseller-portal.customers.index')],
            ['label' => 'Edit ' . $customer->name],
        ];
    }

    public function update()
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $ownerId = auth()->id();
        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->findOrFail($this->customerId);

        $customer->update([
            'name'      => $this->name,
            'phone'     => $this->phone,
            'email'     => $this->email,
            'address'   => $this->address,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'notes'     => $this->notes,
        ]);

        session()->flash('success', 'Data pelanggan berhasil diperbarui!');
        return redirect()->route('reseller-portal.customers.index');
    }

    public function render()
    {
        return view('livewire.reseller-portal.customer.edit');
    }
}
