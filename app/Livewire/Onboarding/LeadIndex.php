<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\Lead;
use Livewire\WithPagination;

class LeadIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $leads = Lead::latest()->paginate(10);
        return view('livewire.onboarding.lead-index', compact('leads'));
    }

    public function convertToProspect($leadId)
    {
        $lead = Lead::findOrFail($leadId);
        $lead->update(['status' => 'converted']);
        session()->flash('success', 'Lead berhasil dikonversi ke Prospecting!');
    }
}
