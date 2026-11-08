<?php

namespace App\Livewire\NOC\Guide;

use Livewire\Component;
use Livewire\Attributes\Url;

class Index extends Component
{
    #[Url]
    public string $activeTab = 'olt'; // olt, acs, mikrotik, alarms, outage

    public function setTab(string $tab)
    {
        $allowed = ['olt', 'acs', 'mikrotik', 'alarms', 'outage'];
        $this->activeTab = in_array($tab, $allowed, true) ? $tab : 'olt';
    }

    public function render()
    {
        return view('livewire.noc.guide.index', [
            'activeTab' => $this->activeTab,
        ])->layout('layouts.app', ['title' => 'SOP & Panduan Operasional NOC - dsBilling']);
    }
}
