<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Show extends AdminComponent
{
    public $customerId;
    public $customer;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $this->customer = Customer::findOrFail($id);
    }

    public function render()
    {
        $timeline = [['date' => now(), 'title' => 'Customer Dibuat', 'description' => 'Customer baru ditambahkan', 'type' => 'create']];
        $activities = [['user' => 'Admin', 'action' => 'Membuat customer baru', 'module' => 'CRM', 'time' => '2 jam lalu']];
        return view('livewire.crm.customer.show', compact('timeline', 'activities'));
    }
}
