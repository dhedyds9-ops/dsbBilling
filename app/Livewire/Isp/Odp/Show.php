<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp as OdpModel;

class Show extends AdminComponent
{
    public $odpId;
    public $odp;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->odpId = $id;
        $this->odp = OdpModel::with(['odc', 'splitters'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => $this->odp->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.odp.show');
    }
}
