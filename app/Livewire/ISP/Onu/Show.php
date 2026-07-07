<?php

namespace App\Livewire\ISP\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu as OnuModel;

class Show extends AdminComponent
{
    public $onuId;
    public $onu;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'onus';
        $this->onuId = $id;
        $this->onu = OnuModel::with(['olt', 'vendor', 'onuPorts'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.onus.index')],
            ['label' => 'ONU', 'url' => route('isp.onus.index')],
            ['label' => $this->onu->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.onu.show');
    }
}
