<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\Quotation;
use Livewire\WithPagination;

class QuotationIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $quotations = Quotation::latest()->paginate(10);

        return view('livewire.onboarding.quotation-index', compact('quotations'));
    }
}
