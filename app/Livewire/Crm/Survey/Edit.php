<?php

namespace App\Livewire\Crm\Survey;

use App\Livewire\AdminComponent;
use App\Models\CRM\Survey;

class Edit extends AdminComponent
{
    public $surveyId;
    public $customer_name = '';
    public $address = '';
    public $survey_date = '';
    public $notes = '';
    public $status = 'scheduled';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'surveys';
        $this->surveyId = $id;
        $survey = Survey::findOrFail($id);
        $this->customer_name = $survey->customer_name;
        $this->address = $survey->address;
        $this->survey_date = $survey->survey_date?->format('Y-m-d');
        $this->notes = $survey->notes;
        $this->status = $survey->status;
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string',
            'survey_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $survey = Survey::findOrFail($this->surveyId);
        $survey->update([
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'survey_date' => $this->survey_date,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Survey berhasil diperbarui!');
        return redirect()->route('crm.surveys.index');
    }

    public function render()
    {
        return view('livewire.crm.survey.edit');
    }
}
