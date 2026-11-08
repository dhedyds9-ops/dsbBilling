<?php

namespace App\Livewire\Inventory\Guide;

use Livewire\Component;
use Livewire\Attributes\Url;

class Index extends Component
{
    #[Url]
    public string $activeSection = 'overview';

    public string $search = '';

    public function setSection(string $section)
    {
        $allowed = ['overview', 'inbound', 'dispatch', 'custody', 'usage', 'return', 'opname', 'troubleshoot'];
        $this->activeSection = in_array($section, $allowed, true) ? $section : 'overview';
    }

    public function render()
    {
        return view('livewire.inventory.guide.index', [
            'activeSection' => $this->activeSection,
        ])->layout('layouts.app', ['title' => 'Panduan Lengkap Operasional Gudang & Inventaris - dsBilling']);
    }
}
