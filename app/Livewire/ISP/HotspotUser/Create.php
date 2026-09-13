<?php

namespace App\Livewire\ISP\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Router;
use App\Services\Provisioning\ProvisioningService;
use App\Services\Auth\UserQueryService;
use App\Enums\UserRole;

class Create extends AdminComponent
{
    // Operator-friendly form fields
    public $name;
    public $phone;
    public $email;
    public $address;
    public $notes;
    public $mac_address;
    public $static_ip;
    public $billing_cycle = 'monthly';
    public $setup_fee;
    public $payment_status = 'unpaid';
    public $reseller_id;
    public $odp_id;
    public $port_number;
    public $onu_id;
    public $latitude;
    public $longitude;

    public $login_method = 'username_and_password';

    public $username;
    public $password;
    public $router_id;
    public $network_profile_id;
    public $service_profile_id;

    public $activation_date;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Hotspot Users', 'url' => route('isp.hotspot-users.index')],
            ['label' => 'Tambah User'],
        ];
        $this->activation_date = now()->format('Y-m-d');
        if (auth()->user()->hasRole(UserRole::Reseller->value)) {
            $this->reseller_id = auth()->id();
        }
    }

    public function updatedLoginMethod($value): void
    {
        if ($value === 'username_only') {
            $this->password = $this->username;
        }
    }

    public function updatedUsername($value): void
    {
        if ($this->login_method === 'username_only') {
            $this->password = $value;
        }
    }

    public function updatedStaticIp($value): void
    {
        $this->validateOnly('static_ip', [
            'static_ip' => 'nullable|ipv4|unique:hotspot_users,static_ip',
        ]);
    }

    public function save(ProvisioningService $provisioningService)
    {
        if ($this->login_method === 'username_only') {
            $this->password = $this->username;
        }

        if (auth()->user()->hasRole(UserRole::Reseller->value)) {
            $this->reseller_id = auth()->id();
        }

        if ($this->mac_address) {
            $this->mac_address = strtoupper(str_replace('-', ':', $this->mac_address));
        }

        // Logic here is modified by me right now: removed network_profile_id validation and changed to router_id
        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'username' => 'required|unique:hotspot_users,username',
            'password' => 'required|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'status' => 'required|in:active,inactive',
            'router_id' => 'nullable|exists:routers,id',
            'mac_address' => 'nullable|string',
            'static_ip' => 'nullable|ip',
            'odp_id' => 'nullable|exists:odps,id',
            'port_number' => 'nullable|integer',
            'onu_id' => 'nullable|string',
            'reseller_id' => 'nullable|exists:users,id',
            'billing_cycle' => 'required|in:monthly,prepaid,postpaid',
            'setup_fee' => 'nullable|numeric'
        ]);

        try {
            $result = $provisioningService->activateHotspotService([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'username' => $this->username,
                'password' => $this->password,
                'service_profile_id' => $this->service_profile_id,
                'status' => $this->status,
                'activation_date' => $this->activation_date,
                'notes' => $this->notes,
                'router_id' => $this->router_id,
                'mac_address' => $this->mac_address,
                'static_ip' => $this->static_ip,
                'odp_id' => $this->odp_id,
                'port_number' => $this->port_number,
                'onu_id' => $this->onu_id,
                'reseller_id' => $this->reseller_id,
                'billing_cycle' => $this->billing_cycle,
                'setup_fee' => $this->setup_fee,
            ], auth()->id());

            session()->flash('success', "User Hotspot berhasil dibuat! Username: {$result['hotspot_user']->username}");
            return redirect()->route('billing.invoices.show', $result['invoice']->id);
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal membuat user: ' . $e->getMessage());
        }
    }

    public function showUsedIps()
    {
        $used = \App\Models\ISP\HotspotUser::whereNotNull('static_ip')->pluck('static_ip')->toArray();
        $message = empty($used) ? 'Belum ada IP Static yang digunakan.' : 'IP yang sudah terpakai: ' . implode(', ', $used);
        session()->flash('info', $message);
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->where('service_type', 'hotspot')->orWhere('service_type', 'combined')->get();
        $routers = Router::active()->get();
        $networkProfiles = \App\Models\Provisioning\NetworkProfile::all();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        $userQueryService = app(UserQueryService::class);
        $resellers = $userQueryService->getResellers();
        return view('livewire.isp.hotspot-user.create', compact('serviceProfiles', 'routers', 'resellers', 'odps', 'onus', 'networkProfiles'));
    }
}
