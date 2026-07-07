<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\QualityControl;
use Livewire\WithPagination;

class QualityControlIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $qualityControls = QualityControl::latest()->paginate(10);

        return view('livewire.onboarding.quality-control-index', compact('qualityControls'));
    }
}
