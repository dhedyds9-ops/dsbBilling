<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\CRM\Prospect;
use Livewire\WithPagination;

class ProspectIndex extends AdminComponent
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $prospects = Prospect::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.onboarding.prospect-index', [
            'prospects' => $prospects
        ]);
    }
}
