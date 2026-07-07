<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;

class Show extends AdminComponent
{
    public $installationId;
    public $installation;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';
        $this->installationId = $id;
        $this->installation = Installation::findOrFail($id);
    }

    public function render()
    {
        $timeline = [['date' => now(), 'title' => 'Installation Dibuat', 'description' => 'Installation baru ditambahkan', 'type' => 'create']];
        $activities = [['user' => 'Admin', 'action' => 'Membuat installation baru', 'module' => 'CRM', 'time' => '2 jam lalu']];
        return view('livewire.crm.installation.show', compact('timeline', 'activities'));
    }
}
