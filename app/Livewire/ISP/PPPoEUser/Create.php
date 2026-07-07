<?php

namespace App\Livewire\ISP\PPPoEUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Router;
use App\Services\Provisioning\ProvisioningService;

class Create extends AdminComponent
{
    // Operator-friendly form fields (MixRadius style)
    public $name;
    public $phone;
    public $email;
    public $address;
    public $notes;

    public $username;
    public $password;
    public $router_id;
    public $service_profile_id;

    public $activation_date;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pppoe-users';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'PPPoE Users', 'url' => route('isp.pppoe-users.index')],
            ['label' => 'Tambah User'],
        ];
        $this->activation_date = now()->format('Y-m-d');
    }

    public function save(ProvisioningService $provisioningService)
    {
        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'username' => 'required|unique:pppoe_users,username',
            'password' => 'required|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $result = $provisioningService->activatePPPoEService([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'username' => $this->username,
                'password' => $this->password,
                'service_profile_id' => $this->service_profile_id,
                'router_id' => $this->router_id,
                'activation_date' => $this->activation_date,
                'status' => $this->status,
                'notes' => $this->notes,
            ], auth()->id());

            session()->flash('success', "User PPPoE berhasil dibuat! Username: {$result['pppoe_user']->username}");
            return redirect()->route('isp.pppoe-users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->where('service_type', 'pppoe')->orWhere('service_type', 'combined')->get();
        $routers = Router::active()->get();
        return view('livewire.isp.pppoe-user.create', compact('serviceProfiles', 'routers'));
    }
}
