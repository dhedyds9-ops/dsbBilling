<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Models\Customer\Contract;
use Livewire\WithPagination;

class ContractIndex extends AdminComponent
{
    use WithPagination;

    public function render()
    {
        $contracts = Contract::latest()->paginate(10);

        return view('livewire.onboarding.contract-index', compact('contracts'));
    }
}
