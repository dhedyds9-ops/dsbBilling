<?php

namespace App\Livewire\ResellerPortal\Customer;

class Active extends Index
{
    public function mount()
    {
        parent::mount();
        $this->activePage = 'customers.active';
        $this->statusFilter = 'active';
    }
}