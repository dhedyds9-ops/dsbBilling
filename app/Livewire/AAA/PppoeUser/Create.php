<?php

namespace App\Livewire\AAA\PppoeUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\InternetPackage;
use App\Models\ISP\ServiceProfile;
use App\Services\Provisioning\ProvisioningService;

class Create extends AdminComponent
{
    // Operator-friendly form fields (MixRadius style)
    public $name;
    public $phone;
    public $email;
    public $address;
    public $coordinates;
    public $notes;

    public $username;
    public $password;
    public $router_nas;
    public $pppoe_server;
    public $internet_package_id;
    public $ppp_profile_id;

    public $activation_date;
    public $billing_date;
    public $status = 'active';
    public $tax;
    public $promo;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'pppoe-users';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Internet', 'url' => route('aaa.pppoe-users.index')],
            ['label' => 'PPPoE Users', 'url' => route('aaa.pppoe-users.index')],
            ['label' => 'Tambah User'],
        ];
        $this->activation_date = now()->format('Y-m-d');
        $this->billing_date = now()->format('Y-m-d');
    }

    public function save(ProvisioningService $provisioningService)
    {
        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'username' => 'required|unique:pppoe_users,username',
            'password' => 'required|string',
            'status' => 'required|in:active,inactive,suspended,terminated',
        ]);

        try {
            $result = $provisioningService->activatePPPoEService([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'username' => $this->username,
                'password' => $this->password,
                'internet_package_id' => $this->internet_package_id,
                'service_profile_id' => $this->ppp_profile_id,
                'activation_date' => $this->activation_date,
                'billing_date' => $this->billing_date,
                'status' => $this->status,
                'notes' => $this->notes,
            ], auth()->id());

            session()->flash('success', "User PPPoE berhasil dibuat! Username: {$result['pppoe_user']->username}");
            return redirect()->route('aaa.pppoe-users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $pppProfiles = ServiceProfile::active()->get();
        $internetPackages = InternetPackage::active()->get();
        return view('livewire.aaa.pppoe-user.create', compact('pppProfiles', 'internetPackages'));
    }
}
