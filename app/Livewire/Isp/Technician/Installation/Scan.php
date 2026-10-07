<?php

namespace App\Livewire\Isp\Technician\Installation;

use Livewire\Component;

class Scan extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.installation.scan')->layout('layouts.noc', ['slot' => '']);
    }
}
