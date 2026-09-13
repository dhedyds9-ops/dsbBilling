<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class ChangeHotspotCredentials extends Component
{
    public $hotspotUserId;
    public $newUsername;
    public $newPassword;
    public $confirmPassword;
    public $message;
    public $messageType;
    public $hotspotUsers;
    public $authType = 'up'; // 'up' = Username & Password, 'vc' = Username=Password

    protected $originalUsername;
    protected $originalPassword;

    protected $rules = [
        'hotspotUserId' => 'required|integer',
        'authType' => 'required|in:up,vc',
        'newUsername' => 'nullable|string|min:3',
        'newPassword' => 'nullable|string|min:3',
        'confirmPassword' => 'nullable|string|same:newPassword',
    ];

    public function mount()
    {
        $customerId = auth()->user()->customer?->id ?? 0;
        $this->hotspotUsers = CustomerService::where('customer_id', $customerId)
            ->where('status', 'active')
            ->with(['hotspotUser.radiusAccountings', 'hotspotUser.customerService'])
            ->get()
            ->pluck('hotspotUser')
            ->filter();

        if ($this->hotspotUsers->isNotEmpty()) {
            $this->hotspotUserId = $this->hotspotUsers->first()->id;
            $this->loadCurrentCredentials();
        }
    }

    public function updatedHotspotUserId()
    {
        $this->loadCurrentCredentials();
    }

    public function updatedAuthType()
    {
        if ($this->authType === 'vc') {
            $this->newPassword = $this->newUsername;
            $this->confirmPassword = $this->newUsername;
        }
    }

    public function updatedNewUsername()
    {
        if ($this->authType === 'vc') {
            $this->newPassword = $this->newUsername;
            $this->confirmPassword = $this->newUsername;
        }
    }

    protected function loadCurrentCredentials()
    {
        $hotspotUser = $this->hotspotUsers->firstWhere('id', $this->hotspotUserId);
        if ($hotspotUser) {
            $this->newUsername = $hotspotUser->username;
            $this->newPassword = $hotspotUser->password;
            $this->confirmPassword = $hotspotUser->password;
            $this->originalUsername = $hotspotUser->username;
            $this->originalPassword = $hotspotUser->password;
            
            if ($hotspotUser->username === $hotspotUser->password) {
                $this->authType = 'vc';
            } else {
                $this->authType = 'up';
            }
        }
    }

    public function saveCredentials(CustomerSelfServiceService $selfService)
    {
        $this->validate();

        $usernameToUpdate = ($this->newUsername !== $this->originalUsername) ? $this->newUsername : null;
        $passwordToUpdate = ($this->newPassword !== $this->originalPassword) ? $this->newPassword : null;

        if (!$usernameToUpdate && !$passwordToUpdate) {
            $this->message = 'Tidak ada perubahan pada username atau password';
            $this->messageType = 'error';
            return;
        }

        $result = $selfService->changeHotspotCredentials(
            auth()->user()->customer?->id ?? 0,
            $this->hotspotUserId,
            $usernameToUpdate,
            $passwordToUpdate
        );

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            $this->loadCurrentCredentials();
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-hotspot-credentials');
    }
}
