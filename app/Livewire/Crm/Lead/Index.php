<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Lead;
use App\Services\Onboarding\CustomerOnboardingService;

class Index extends BaseCrmComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
        $this->filters = ['status' => ''];
    }

    public function delete($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        session()->flash('success', 'Calon User berhasil dihapus!');
    }

    public function convertToProspect($id, CustomerOnboardingService $onboarding)
    {
        $lead = Lead::findOrFail($id);
        if ($lead->status === 'converted') {
            session()->flash('warning', 'Calon User ini sudah pernah dikonversi.');
            return;
        }
        $onboarding->convertLeadToProspect($lead->id, auth()->id());
        session()->flash('success', 'Calon User berhasil dikonversi ke Prospecting! Lanjutkan Survei & Instalasi.');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Lead::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $leads = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage === 'all' ? 999999 : $this->perPage);

        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'followup' => Lead::whereIn('status', ['contacted', 'qualified'])->count(),
            'converted' => Lead::where('status', 'converted')->count(),
        ];

        return view('livewire.crm.lead.index', compact('leads', 'stats'));
    }
}
