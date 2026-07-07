<?php

namespace App\Livewire\Crm\Contract;

use App\Livewire\AdminComponent;
use App\Models\CRM\Contract;

class Show extends AdminComponent
{
    public $contractId;
    public $contract;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'contracts';
        $this->contractId = $id;
        $this->contract = Contract::findOrFail($id);
    }

    public function render()
    {
        $timeline = [['date' => now(), 'title' => 'Contract Dibuat', 'description' => 'Contract baru ditambahkan', 'type' => 'create']];
        $activities = [['user' => 'Admin', 'action' => 'Membuat contract baru', 'module' => 'CRM', 'time' => '2 jam lalu']];
        return view('livewire.crm.contract.show', compact('timeline', 'activities'));
    }
}
