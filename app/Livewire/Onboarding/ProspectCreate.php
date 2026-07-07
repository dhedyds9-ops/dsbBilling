<?php

namespace App\Livewire\Onboarding;

use App\Livewire\AdminComponent;
use App\Services\Onboarding\CustomerOnboardingService;
use Illuminate\Support\Facades\Auth;

class ProspectCreate extends AdminComponent
{
    public $name = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $province = '';
    public $city = '';
    public $district = '';
    public $village = '';
    public $notes = '';

    public function save(CustomerOnboardingService $service)
    {
        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $service->createProspect([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'village' => $this->village,
            'notes' => $this->notes,
        ], Auth::id());

        $this->redirect(route('onboarding.prospects.index'));
    }

    public function render()
    {
        return view('livewire.onboarding.prospect-create');
    }
}
