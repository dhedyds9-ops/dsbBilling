<?php

namespace App\Livewire\Isp\PPPoEUser;

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
    public $login_method = 'username_and_password';
    public $router_id;
    public $service_profile_id;
    public $network_profile_id;

    public $mac_address;
    public $static_ip;
    public $genieacs_device_id;
    public $odp_id;
    public $port_number;
    public $onu_id;
    public $latitude;
    public $longitude;

    public $billing_cycle = 'monthly';
    public $setup_fee;
    public $payment_status = 'unpaid';
    public $reseller_id;

    public $activation_date;
    public $status = 'active';

    public function mount()
    {
        $this->authorize('create', \App\Models\ISP\PPPoEUser::class);
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

    public function updatedLoginMethod($value)
    {
        if ($value === 'username_only') {
            $this->password = $this->username;
        } else {
            $this->password = '';
        }
    }

    public function updatedUsername($value)
    {
        if ($this->login_method === 'username_only') {
            $this->password = $value;
        }
    }

    public function save(ProvisioningService $provisioningService)
    {
        if ($this->login_method === 'username_only') {
            $this->password = $this->username;
        }

        $this->authorize('create', \App\Models\ISP\PPPoEUser::class);

        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'notes' => 'nullable|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'status' => 'required|in:active,inactive',
            'router_id' => 'nullable|exists:routers,id',
            'mac_address' => 'nullable|string',
            'static_ip' => 'nullable|ip|unique:pppoe_users,static_ip',
            'odp_id' => 'nullable|exists:odps,id',
            'port_number' => 'nullable|integer',
            'onu_id' => 'nullable|string',
            'reseller_id' => 'nullable|exists:users,id',
            'billing_cycle' => 'required|in:monthly,prepaid,postpaid',
            'setup_fee' => 'nullable|numeric'
        ]);

        try {
            \App\Services\CRM\CustomerValidationService::validateBeforeCreate([
                'phone' => $this->phone,
                'email' => $this->email,
                'username' => $this->username,
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Nomor HP')) {
                $this->addError('phone', $msg);
            } elseif (str_contains($msg, 'Username')) {
                $this->addError('username', $msg);
            } elseif (str_contains($msg, 'Email')) {
                $this->addError('email', $msg);
            } else {
                session()->flash('error', $msg);
            }
            return;
        }

        try {
            $actualOnuId = null;
            if ($this->onu_id) {
                if (is_numeric($this->onu_id)) {
                    $actualOnuId = (int) $this->onu_id;
                } else {
                    $onu = \App\Models\ISP\Onu::where('serial_number', $this->onu_id)
                        ->orWhere('mac_address', $this->onu_id)
                        ->orWhere('code', $this->onu_id)
                        ->first();
                    if ($onu) {
                        $actualOnuId = $onu->id;
                    } else {
                        // Daftarkan ONU baru secara otomatis
                        try {
                            $createdOnu = \App\Models\ISP\Onu::create([
                                'code' => 'ONU-' . strtoupper(substr(md5($this->onu_id . time()), 0, 6)),
                                'name' => 'ONU ' . $this->name,
                                'serial_number' => $this->onu_id,
                                'status' => 'active',
                                'created_by' => auth()->id(),
                            ]);
                            $actualOnuId = $createdOnu->id;
                        } catch (\Throwable $e) {
                            $actualOnuId = null;
                        }
                    }
                }
            }

            $result = $provisioningService->activatePPPoEService([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
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
                'onu_id' => $actualOnuId,
                'reseller_id' => $this->reseller_id,
                'billing_cycle' => $this->billing_cycle,
                'setup_fee' => $this->setup_fee,
            ], auth()->id());

            session()->flash('success', "User PPPoE berhasil dibuat! Username: {$result['pppoe_user']->username}");
            return redirect()->route('billing.invoices.show', $result['invoice']->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat user: ' . $e->getMessage());
        }
    }

    public function showUsedIps()
    {
        $used = \App\Models\ISP\PPPoEUser::whereNotNull('static_ip')->pluck('static_ip')->toArray();
        $message = empty($used) ? 'Belum ada IP Static yang digunakan.' : 'IP yang sudah terpakai: ' . implode(', ', $used);
        session()->flash('info', $message);
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()
            ->where(fn($q) => $q->where('service_type', 'pppoe')->orWhere('service_type', 'combined'))
            ->get();
        $routers = Router::active()->get();
        $networkProfiles = \App\Models\Provisioning\NetworkProfile::all();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        $resellers = $userQueryService->getResellers();

        return view('livewire.isp.pppoe-user.create', compact('serviceProfiles', 'routers', 'networkProfiles', 'odps', 'onus', 'resellers'));
    }
}
