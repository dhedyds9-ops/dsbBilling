<?php

namespace App\Livewire\ISP\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt as OltModel;

class Show extends AdminComponent
{
    public $oltId;
    public $olt;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'olts';
        $this->oltId = $id;
        $this->olt = OltModel::with(['pop', 'vendor', 'ponPorts', 'onus'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.olts.index')],
            ['label' => 'OLT', 'url' => route('isp.olts.index')],
            ['label' => $this->olt->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.olt.show');
    }
}
