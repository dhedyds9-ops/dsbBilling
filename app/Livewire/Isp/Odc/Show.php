<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc as OdcModel;

class Show extends AdminComponent
{
    public $odcId;
    public $odc;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odcs';
        $this->odcId = $id;
        $this->odc = OdcModel::with(['olt', 'pop', 'odps'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => $this->odc->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.odc.show');
    }
}
