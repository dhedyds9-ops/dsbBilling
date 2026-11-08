<?php

namespace App\Livewire\CustomerPortal\Guide;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

class Index extends Component
{
    #[Url]
    public string $activeTab = 'bayar'; // bayar, wifi, tiket, paket, tips

    public function setTab(string $tab)
    {
        $allowed = ['bayar', 'wifi', 'tiket', 'paket', 'tips'];
        $this->activeTab = in_array($tab, $allowed, true) ? $tab : 'bayar';
    }

    public function render()
    {
        return view('livewire.customer-portal.guide.index', [
            'activeTab' => $this->activeTab,
        ])->layout('layouts.app', ['title' => 'Panduan & Bantuan Pelanggan - dsBilling']);
    }
}
