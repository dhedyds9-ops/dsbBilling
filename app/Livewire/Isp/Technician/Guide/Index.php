<?php

namespace App\Livewire\Isp\Technician\Guide;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    #[Url]
    public string $activeTab = 'absensi'; // absensi, jobs, psb, material, odp, payroll, troubleshooting

    public function setTab(string $tab)
    {
        $allowed = ['absensi', 'jobs', 'psb', 'material', 'odp', 'payroll', 'troubleshooting'];
        $this->activeTab = in_array($tab, $allowed, true) ? $tab : 'absensi';
    }

    public function render()
    {
        return view('livewire.isp.technician.guide.index', [
            'activeTab' => $this->activeTab,
        ]);
    }
}
