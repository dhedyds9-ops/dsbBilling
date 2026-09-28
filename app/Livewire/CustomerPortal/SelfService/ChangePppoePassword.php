<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class ChangePppoePassword extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div></div>
        HTML;
    }
}

