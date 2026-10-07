<?php

namespace App\Livewire\Isp\Technician\Odp;

use Livewire\Component;

class Search extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.odp.search')->layout('layouts.noc', ['slot' => '']);
    }
}
