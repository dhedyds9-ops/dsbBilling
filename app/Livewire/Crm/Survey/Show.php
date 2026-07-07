<?php

namespace App\Livewire\Crm\Survey;

use App\Livewire\AdminComponent;
use App\Models\CRM\Survey;

class Show extends AdminComponent
{
    public $surveyId;
    public $survey;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'surveys';
        $this->surveyId = $id;
        $this->survey = Survey::findOrFail($id);
    }

    public function render()
    {
        $timeline = [
            ['date' => now(), 'title' => 'Survey Dibuat', 'description' => 'Survey baru ditambahkan ke sistem', 'type' => 'create'],
        ];
        $activities = [
            ['user' => 'Admin', 'action' => 'Membuat survey baru', 'module' => 'CRM', 'time' => '2 jam lalu'],
        ];
        return view('livewire.crm.survey.show', compact('timeline', 'activities'));
    }
}
