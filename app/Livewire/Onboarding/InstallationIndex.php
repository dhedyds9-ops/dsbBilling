<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;
use Livewire\WithPagination;

class InstallationIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $installations = Installation::latest()->paginate(10);

        return view('livewire.onboarding.installation-index', compact('installations'));
    }
}
