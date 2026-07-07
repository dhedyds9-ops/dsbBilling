<?php

namespace App\Livewire\ISP\Tower;

use App\Livewire\AdminComponent;
use App\Models\ISP\Tower as TowerModel;

class Show extends AdminComponent
{
    public $towerId;
    public $tower;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'towers';
        $this->towerId = $id;
        $this->tower = TowerModel::with(['pops', 'accessPoints'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.towers.index')],
            ['label' => 'Towers', 'url' => route('isp.towers.index')],
            ['label' => $this->tower->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.tower.show');
    }
}
