<?php

namespace App\Livewire\CustomerPortal\Profile;

use App\Services\CustomerPortal\CustomerProfileService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class Profile extends Component
{
    public $name;
    public $email;
    public $phone;
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;
    public $message;
    public $messageType;

    public function mount(CustomerProfileService $profileService)
    {
        $profile = $profileService->getProfile(Auth::id());
        if ($profile) {
            $this->name = $profile->name;
            $this->email = $profile->email;
            $this->phone = $profile->phone;
        }
    }

    public function updateProfile(CustomerProfileService $profileService)
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $result = $profileService->updateProfile(Auth::id(), [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';
    }

    public function changePassword(CustomerProfileService $profileService)
    {
        $this->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:6',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        $result = $profileService->changePassword(Auth::id(), $this->currentPassword, $this->newPassword);

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';
        $this->reset(['currentPassword', 'newPassword', 'confirmPassword']);
    }

    public function render()
    {
        return view('livewire.customer-portal.profile.profile');
    }
}

