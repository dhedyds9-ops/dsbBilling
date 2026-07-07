<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangePppoePassword extends Component
{
    public $newPassword;
    public $confirmPassword;
    public $message;
    public $messageType;

    protected $rules = [
        'newPassword' => 'required|min:6',
        'confirmPassword' => 'required|same:newPassword',
    ];

    public function savePassword(CustomerSelfServiceService $selfService)
    {
        $this->validate();
        $result = $selfService->changePppoePassword(Auth::id(), $this->newPassword);
        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';
        $this->reset(['newPassword', 'confirmPassword']);
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-pppoe-password');
    }
}

