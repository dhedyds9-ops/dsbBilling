<?php

namespace App\Livewire\Admin\Guide;

use Livewire\Component;
use Livewire\Attributes\Url;

class Index extends Component
{
    #[Url]
    public string $activeRoleTab = 'admin'; // admin, kasir, cs, manager

    public function setRoleTab(string $tab)
    {
        $allowed = ['admin', 'kasir', 'cs', 'manager'];
        $this->activeRoleTab = in_array($tab, $allowed, true) ? $tab : 'admin';
    }

    public function render()
    {
        return view('livewire.admin.guide.index', [
            'activeRoleTab' => $this->activeRoleTab,
        ])->layout('layouts.app', ['title' => 'Pusat Panduan & SOP Operasional Staf - dsBilling']);
    }
}
