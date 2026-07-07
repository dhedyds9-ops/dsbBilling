<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Activation;

class Show extends AdminComponent
{
    public $activationId;
    public $activation;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'activations';
        $this->activationId = $id;
        $this->activation = Activation::findOrFail($id);
    }

    public function render()
    {
        $timeline = [['date' => now(), 'title' => 'Activation Dibuat', 'description' => 'Activation baru ditambahkan', 'type' => 'create']];
        $activities = [['user' => 'Admin', 'action' => 'Membuat activation baru', 'module' => 'CRM', 'time' => '2 jam lalu']];
        return view('livewire.crm.activation.show', compact('timeline', 'activities'));
    }
}
