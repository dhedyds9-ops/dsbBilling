<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\Survey;
use Livewire\WithPagination;

class SurveyIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $surveys = Survey::latest()->paginate(10);

        return view('livewire.onboarding.survey-index', compact('surveys'));
    }
}
