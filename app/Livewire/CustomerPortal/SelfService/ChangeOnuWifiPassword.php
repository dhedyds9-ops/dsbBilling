<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangeOnuWifiPassword extends Component
{
    public $newPassword;
    public $confirmPassword;
    public $message;
    public $messageType;
    public $onuId;
    public $customerServices;

    protected $rules = [
        'onuId' => 'required|integer',
        'newPassword' => 'required|min:8',
        'confirmPassword' => 'required|same:newPassword',
    ];

    public function mount()
    {
        // Load customer's active services with ONU
        $this->customerServices = CustomerService::where('customer_id', Auth::id())
            ->where('status', 'active')
            ->whereNotNull('onu_id')
            ->with('onu')
            ->get();
        
        // Set default ONU if available
        if ($this->customerServices->isNotEmpty()) {
            $this->onuId = $this->customerServices->first()->onu_id;
        }
    }

    public function savePassword(CustomerSelfServiceService $selfService)
    {
        $this->validate();
        
        $result = $selfService->changeOnuWifiPassword(Auth::id(), $this->onuId, $this->newPassword);
        
        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->reset(['newPassword', 'confirmPassword']);
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-onu-wifi-password');
    }
}
