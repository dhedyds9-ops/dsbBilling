<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangeHotspotCredentials extends Component
{
    public $hotspotUserId;
    public $newUsername;
    public $newPassword;
    public $confirmPassword;
    public $message;
    public $messageType;
    public $hotspotUsers;

    protected $rules = [
        'hotspotUserId' => 'required|integer',
        'newUsername' => 'nullable|string|min:3',
        'newPassword' => 'nullable|string|min:6',
        'confirmPassword' => 'nullable|string|same:newPassword',
    ];

    public function mount()
    {
        // Load customer's active hotspot users
        $this->hotspotUsers = CustomerService::where('customer_id', Auth::id())
            ->where('status', 'active')
            ->with('hotspotUsers')
            ->get()
            ->pluck('hotspotUsers')
            ->flatten()
            ->filter();

        // Set default if available
        if ($this->hotspotUsers->isNotEmpty()) {
            $this->hotspotUserId = $this->hotspotUsers->first()->id;
        }
    }

    public function saveCredentials(CustomerSelfServiceService $selfService)
    {
        $this->validate();

        // Ensure at least one field is being changed
        if (!$this->newUsername && !$this->newPassword) {
            $this->message = 'Silakan masukkan username baru atau password baru';
            $this->messageType = 'error';
            return;
        }

        $result = $selfService->changeHotspotCredentials(
            Auth::id(),
            $this->hotspotUserId,
            $this->newUsername,
            $this->newPassword
        );

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            $this->reset(['newUsername', 'newPassword', 'confirmPassword']);
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-hotspot-credentials');
    }
}
