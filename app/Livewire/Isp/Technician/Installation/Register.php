<?php

namespace App\Livewire\Isp\Technician\Installation;

use Livewire\Component;

class Register extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.installation.register')->layout('layouts.noc', ['slot' => '']);
    }
}
