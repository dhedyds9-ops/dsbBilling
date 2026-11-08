<?php

namespace App\Livewire\ResellerPortal\Guide;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

class Index extends Component
{
    #[Url]
    public string $activeTab = 'pelanggan'; // pelanggan, keuangan, voucher, material, jaringan, tickets

    public function setTab(string $tab)
    {
        $allowed = ['pelanggan', 'keuangan', 'voucher', 'material', 'jaringan', 'tickets'];
        $this->activeTab = in_array($tab, $allowed, true) ? $tab : 'pelanggan';
    }

    public function render()
    {
        return view('livewire.reseller-portal.guide.index', [
            'activeTab' => $this->activeTab,
        ])->layout('layouts.app', ['title' => 'Panduan Lengkap Mitra Reseller - dsBilling']);
    }
}
