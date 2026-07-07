<?php

namespace App\Livewire\Crm\Survey;

use App\Livewire\AdminComponent;
use App\Models\CRM\Survey;

class Create extends AdminComponent
{
    public $customer_name = '';
    public $address = '';
    public $survey_date = '';
    public $notes = '';
    public $status = 'scheduled';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'surveys';
        $this->survey_date = now()->format('Y-m-d');
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

        Survey::create([
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'survey_date' => $this->survey_date,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Survey berhasil dibuat!');
        return redirect()->route('crm.surveys.index');
    }

    public function render()
    {
        return view('livewire.crm.survey.create');
    }
}
