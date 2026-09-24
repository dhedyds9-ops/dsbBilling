<?php

namespace App\Livewire\ResellerPortal\Customer;

class Isolated extends Index
{
    public function mount()
    {
        parent::mount();
        $this->activePage = 'customers.isolated';
        $this->statusFilter = 'suspend';
    }
}