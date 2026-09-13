<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Edit extends AdminComponent
{
    public $customerId;
    public $customer;
    
    // basic fields that might exist in blade
    public $name = '';
    public $customer_code = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $latitude = '';
    public $longitude = '';
    public $status = '';
    public $service_profile_id;
    public $router_id;
    public $pppoe_username = '';
    public $pppoe_password = '';
    public $reseller_id = '';
    public $branch_id = '';
    public $notes = '';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $this->customer = Customer::findOrFail($id);
        
        $this->name = $this->customer->name;
        $this->customer_code = $this->customer->customer_code;
        $this->email = $this->customer->email;
        $this->phone = $this->customer->phone;
        $this->address = $this->customer->address;
        $this->latitude = $this->customer->latitude;
        $this->longitude = $this->customer->longitude;
        $this->status = $this->customer->status;
        $this->reseller_id = $this->customer->reseller_id;
        $this->branch_id = $this->customer->branch_id;
        $this->notes = $this->customer->notes;
    }

    public function render()
    {
        return view('livewire.crm.customer.edit');
    }
}
