<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;

class Show extends AdminComponent
{
    public $oltId;
    public $olt;

    public function mount($id = null)
    {
        parent::mount();
        $this->oltId = $id;
        $this->olt = Olt::forReseller()
            ->with([
                'pop',
                'vendor',
                'branch',
                'ponPorts' => fn ($pq) => $pq->orderBy('port_number', 'asc'),
                'ponPorts.onus' => fn ($oq) => $oq->forReseller(),
                'onus' => fn ($oq) => $oq->forReseller(),
            ])
            ->findOrFail($id);

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data OLT', 'url' => route('reseller-portal.network.olts.index')],
            ['label' => $this->olt->name, 'url' => '#'],
        ];
    }

    public function render()
    {
        return view('livewire.reseller-portal.network.olt.show');
    }
}
