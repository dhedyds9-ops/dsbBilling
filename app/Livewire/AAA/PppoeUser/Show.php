<?php

namespace App\Livewire\AAA\PppoeUser;

use App\Livewire\AdminComponent;
use App\Models\AAA\PPPoEUser;

class Show extends AdminComponent
{
    public $pppoeUserId;
    public $pppoeUser;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'pppoe-users';
        $this->pppoeUserId = $id;
        $this->pppoeUser = PPPoEUser::with(['customerService', 'serviceProfile', 'ipAllocation', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.pppoe-users.index')],
            ['label' => 'PPPoE Users', 'url' => route('aaa.pppoe-users.index')],
            ['label' => $this->pppoeUser->username],
        ];
    }

    public function render()
    {
        return view('livewire.aaa.pppoe-user.show');
    }
}
