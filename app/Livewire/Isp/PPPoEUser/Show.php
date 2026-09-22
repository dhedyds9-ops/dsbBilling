<?php

namespace App\Livewire\ISP\PPPoEUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\PPPoEUser;

class Show extends AdminComponent
{
    public $pppoeUserId;
    public $pppoeUser;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pppoe-users';
        $this->pppoeUserId = $id;
        $this->pppoeUser = PPPoEUser::with(['customerService', 'serviceProfile', 'ipAllocation', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.pppoe-users.index')],
            ['label' => 'PPPoE Users', 'url' => route('isp.pppoe-users.index')],
            ['label' => $this->pppoeUser->username],
        ];
    }

    public function render()
    {
        return view('livewire.isp.pppoe-user.show');
    }
}
