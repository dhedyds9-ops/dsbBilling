<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\CoverageCheck;
use Livewire\WithPagination;

class CoverageCheckIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $coverageChecks = CoverageCheck::latest()->paginate(10);

        return view('livewire.onboarding.coverage-check-index', compact('coverageChecks'));
    }
}
