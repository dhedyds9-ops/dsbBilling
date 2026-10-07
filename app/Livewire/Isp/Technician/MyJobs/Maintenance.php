<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use Livewire\Component;

class Maintenance extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.my-jobs.maintenance')->layout('layouts.noc', ['slot' => '']);
    }
}
