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
        $this->installation = Installation::with(['customer', 'survey.prospect', 'assignedTo', 'onu', 'createdBy'])->findOrFail($id);

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'CRM', 'url' => '#'],
            ['label' => 'Instalasi', 'url' => route('crm.installations.index')],
            ['label' => 'Detail Instalasi #' . $this->installation->id, 'url' => '#'],
        ];
    }

    public function render()
    {
        return view('livewire.crm.installation.show');
    }
}
