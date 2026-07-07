<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\AdminComponent;
use App\Models\CRM\Lead;

class Show extends AdminComponent
{
    public $leadId;
    public $lead;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
        $this->leadId = $id;
        $this->lead = Lead::findOrFail($id);
    }

    public function render()
    {
        // Sample timeline and activity data
        $timeline = [
            [
                'date' => now(),
                'title' => 'Lead Dibuat',
                'description' => 'Lead baru ditambahkan ke sistem',
                'type' => 'create',
            ],
            [
                'date' => now()->subDays(1),
                'title' => 'Dihubungi',
                'description' => 'Tim sales telah menghubungi lead via telepon',
                'type' => 'contact',
            ],
        ];

        $activities = [
            [
                'user' => 'Admin',
                'action' => 'Membuat lead baru',
                'module' => 'CRM',
                'time' => '2 jam lalu',
            ],
            [
                'user' => 'Sales 1',
                'action' => 'Mengupdate status lead menjadi contacted',
                'module' => 'CRM',
                'time' => '1 hari lalu',
            ],
        ];

        return view('livewire.crm.lead.show', compact('timeline', 'activities'));
    }
}
