<?php

namespace App\Livewire\AAA\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\AAA\HotspotUser;

class Show extends AdminComponent
{
    public $hotspotUserId;
    public $hotspotUser;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'hotspot-users';
        $this->hotspotUserId = $id;
        $this->hotspotUser = HotspotUser::with(['customerService', 'serviceProfile', 'voucherPool', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.hotspot-users.index')],
            ['label' => 'Hotspot Users', 'url' => route('aaa.hotspot-users.index')],
            ['label' => $this->hotspotUser->username],
        ];
    }

    public function render()
    {
        return view('livewire.aaa.hotspot-user.show');
    }
}
