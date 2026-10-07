<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use Livewire\Component;

class Psb extends Component
{
    public function render()
    {
        return view('livewire.isp.technician.my-jobs.psb')->layout('layouts.noc', ['slot' => '']);
    }
}
