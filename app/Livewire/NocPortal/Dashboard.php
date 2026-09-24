<?php

namespace App\Livewire\NocPortal;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.noc-portal.dashboard')->layout('layouts.noc');
    }
}
