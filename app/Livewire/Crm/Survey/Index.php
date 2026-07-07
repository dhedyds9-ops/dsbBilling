<?php

namespace App\Livewire\Crm\Survey;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Survey;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'surveys';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $survey = Survey::findOrFail($id);
        $survey->delete();
        session()->flash('success', 'Survey berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Survey::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $surveys = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        return view('livewire.crm.survey.index', compact('surveys'));
    }
}
