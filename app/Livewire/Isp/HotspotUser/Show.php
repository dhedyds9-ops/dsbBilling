<?php

namespace App\Livewire\Isp\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\HotspotUser;

class Show extends AdminComponent
{
    public $hotspotUserId;
    public $hotspotUser;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->hotspotUserId = $id;
        $this->hotspotUser = HotspotUser::with(['customerService', 'serviceProfile', 'voucherPool', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.hotspot-users.index')],
            ['label' => 'Hotspot Users', 'url' => route('isp.hotspot-users.index')],
            ['label' => $this->hotspotUser->username],
        ];
    }

    public function render()
    {
        return view('livewire.isp.hotspot-user.show');
    }
}
