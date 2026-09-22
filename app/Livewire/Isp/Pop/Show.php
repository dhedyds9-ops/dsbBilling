<?php

namespace App\Livewire\Isp\Pop;

use App\Livewire\AdminComponent;
use App\Models\ISP\Pop as PopModel;

class Show extends AdminComponent
{
    public $popId;
    public $pop;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pops';
        $this->popId = $id;
        $this->pop = PopModel::with(['tower', 'olts', 'odcs', 'routers', 'switches'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.pops.index')],
            ['label' => 'POPs', 'url' => route('isp.pops.index')],
            ['label' => $this->pop->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.pop.show');
    }
}
