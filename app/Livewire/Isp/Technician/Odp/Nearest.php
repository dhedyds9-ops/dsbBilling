<?php

namespace App\Livewire\Isp\Technician\Odp;

use Livewire\Component;

class Nearest extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.odp.nearest')->layout('layouts.noc', ['slot' => '']);
    }
}
