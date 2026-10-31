<?php
namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use Illuminate\Support\Str;

class Edit extends AdminComponent
{
    public $customerId;
    
    // Section 1: Customer Profile
    public $name;
    public $phone;
    public $email;
    public $address;
    public $latitude;
    public $longitude;
    public $notes;

    // Section 2: Service & Network
    public $service_type = 'pppoe'; // 'pppoe' or 'hotspot'
    public $login_method = 'username_and_password';
    public $username;
    public $password;
    public $router_id;
    public $service_profile_id;
    public $static_ip;
    public $mac_address;
    public $odp_id;
    public $port_number;
    public $onu_id; // ONU Serial Number or ID

    // Section 3: Billing & Status
    public $status = 'active';
    public $billing_cycle = 'monthly';
    public $setup_fee = 0;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        
        $ownerId = auth()->user()->getEffectiveResellerId();
        $customer = Customer::with([
            'customerServices.serviceProfile',
            'customerServices.onu',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
        ])
        ->where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->latitude = $customer->latitude;
        $this->longitude = $customer->longitude;
        $this->notes = $customer->notes;
        $this->status = $customer->status ?? 'active';

        $cs = $customer->customerServices->first();
        $pppoe = $cs?->pppoeUser;
        $hotspot = $cs?->hotspotUser;

        if ($hotspot && !$pppoe) {
            $this->service_type = 'hotspot';
            $userModel = $hotspot;
        } else {
            $this->service_type = 'pppoe';
            $userModel = $pppoe;
        }

        $this->username = $userModel?->username ?? $cs?->username ?? '';
        $this->password = $userModel?->password ?? $cs?->password ?? '';
        $this->service_profile_id = $userModel?->service_profile_id ?? $cs?->service_profile_id ?? '';
        $this->router_id = $userModel?->router_id ?? '';
        $this->static_ip = $userModel?->static_ip ?? '';
        $this->mac_address = $userModel?->mac_address ?? '';
        $this->odp_id = $userModel?->odp_id ?? '';
        $this->port_number = $userModel?->port_number ?? '';
        $this->billing_cycle = $userModel?->billing_cycle ?? 'monthly';
        $this->setup_fee = $userModel?->setup_fee ?? 0;
        $this->onu_id = $cs?->onu?->serial_number ?? $customer->onu_device['sn'] ?? '';

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => route('reseller-portal.customers.index')],
            ['label' => 'Edit ' . $customer->name],
        ];
    }

    public function updatedLoginMethod($val)
    {
        if ($val === 'username_only') {
            $this->password = $this->username;
        }
    }

    public function updatedUsername($val)
    {
        if ($this->login_method === 'username_only') {
            $this->password = $val;
        }
    }

    public function save()
    {
        return $this->update();
    }

    public function update()
    {
        if ($this->login_method === 'username_only') {
            $this->password = $this->username;
        }

        $this->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'status'             => 'required|in:active,inactive,suspended,suspend',
            'service_type'       => 'required|in:pppoe,hotspot',
            'username'           => 'nullable|string|max:100',
            'password'           => 'nullable|string|max:100',
            'service_profile_id' => 'nullable|exists:service_profiles,id',
            'router_id'          => 'nullable|exists:routers,id',
            'static_ip'          => 'nullable|string|max:45',
            'mac_address'        => 'nullable|string|max:50',
            'odp_id'             => 'nullable|exists:odps,id',
            'port_number'        => 'nullable|integer',
            'onu_id'             => 'nullable|string|max:100',
            'billing_cycle'      => 'nullable|string',
            'setup_fee'          => 'nullable|numeric',
        ]);

        $ownerId = auth()->user()->getEffectiveResellerId();
        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->findOrFail($this->customerId);

        if ($this->phone) {
            try {
                \App\Services\CRM\CustomerValidationService::validatePhone($this->phone, $customer->id, $customer->user_id);
            } catch (\InvalidArgumentException $e) {
                $this->addError('phone', $e->getMessage());
                session()->flash('error', $e->getMessage());
                return;
            }
        }

        // 1. Update Customer Profile
        $customer->update([
            'name'      => $this->name,
            'phone'     => $this->phone,
            'email'     => $this->email,
            'status'    => $this->status,
            'address'   => $this->address,
            'latitude'  => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'notes'     => $this->notes,
        ]);

        // 2. Resolve / Register ONU
        $actualOnuId = null;
        if (!empty(trim((string)$this->onu_id))) {
            $cleanSn = strtoupper(trim($this->onu_id));
            $onu = Onu::where('serial_number', $cleanSn)
                ->orWhere('id', $cleanSn)
                ->first();

            if (!$onu) {
                $onu = Onu::create([
                    'name' => 'ONU-' . $cleanSn,
                    'code' => 'ONU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cleanSn), -6)),
                    'model' => 'ONT',
                    'serial_number' => $cleanSn,
                    'status' => 'active',
                    'branch_id' => $customer->branch_id,
                    'created_by' => auth()->id(),
                ]);
            }
            $actualOnuId = $onu->id;
        }

        // 3. Update / Create CustomerService
        $cs = $customer->customerServices()->first();
        $serviceData = [
            'service_profile_id' => $this->service_profile_id ?: null,
            'onu_id'             => $actualOnuId,
            'status'             => $this->status,
        ];
        if (!empty($this->username)) {
            $serviceData['username'] = $this->username;
        }
        if (!empty($this->password)) {
            $serviceData['password'] = $this->password;
        }

        if (!$cs) {
            $cs = $customer->customerServices()->create(array_merge([
                'uuid'        => (string) Str::uuid(),
                'service_id'  => 1,
                'contract_id' => 1,
                'username'    => $this->username ?: 'user_' . $customer->id,
                'password'    => $this->password ?: '123456',
                'reseller_id' => $customer->reseller_id,
                'created_by'  => auth()->id(),
            ], $serviceData));
        } else {
            $cs->update($serviceData);
        }

        // 4. Update / Create PPPoE or Hotspot User
        $formattedMac = $this->mac_address ? strtoupper(str_replace('-', ':', trim($this->mac_address))) : null;

        if ($this->service_type === 'pppoe') {
            $pppoe = $cs->pppoeUser ?? PPPoEUser::where('customer_service_id', $cs->id)->first();
            $pppoeData = [
                'username'           => $this->username ?: $cs->username,
                'service_profile_id' => $this->service_profile_id ?: null,
                'router_id'          => $this->router_id ?: null,
                'static_ip'          => $this->static_ip ?: null,
                'mac_address'        => $formattedMac,
                'odp_id'             => $this->odp_id ?: null,
                'port_number'        => $this->port_number ?: null,
                'billing_cycle'      => $this->billing_cycle ?: 'monthly',
                'setup_fee'          => $this->setup_fee ?: 0,
                'status'             => $this->status,
            ];
            if (!empty($this->password)) {
                $pppoeData['password'] = $this->password;
            }

            if ($pppoe) {
                $pppoe->update($pppoeData);
            } else {
                $pppoeData['customer_service_id'] = $cs->id;
                $pppoeData['uuid'] = (string) Str::uuid();
                $pppoeData['created_by'] = auth()->id();
                $pppoeData['reseller_id'] = $customer->reseller_id;
                $pppoeData['password'] = $this->password ?: '123456';
                PPPoEUser::create($pppoeData);
            }
        } elseif ($this->service_type === 'hotspot') {
            $hotspot = $cs->hotspotUser ?? HotspotUser::where('customer_service_id', $cs->id)->first();
            $hotspotData = [
                'username'           => $this->username ?: $cs->username,
                'service_profile_id' => $this->service_profile_id ?: null,
                'router_id'          => $this->router_id ?: null,
                'static_ip'          => $this->static_ip ?: null,
                'mac_address'        => $formattedMac,
                'odp_id'             => $this->odp_id ?: null,
                'port_number'        => $this->port_number ?: null,
                'billing_cycle'      => $this->billing_cycle ?: 'monthly',
                'setup_fee'          => $this->setup_fee ?: 0,
                'status'             => $this->status,
            ];
            if (!empty($this->password)) {
                $hotspotData['password'] = $this->password;
            }

            if ($hotspot) {
                $hotspot->update($hotspotData);
            } else {
                $hotspotData['customer_service_id'] = $cs->id;
                $hotspotData['uuid'] = (string) Str::uuid();
                $hotspotData['created_by'] = auth()->id();
                $hotspotData['reseller_id'] = $customer->reseller_id;
                $hotspotData['password'] = $this->password ?: '123456';
                HotspotUser::create($hotspotData);
            }
        }

        session()->flash('success', 'Data pelanggan, akun layanan, dan perangkat ONU berhasil diperbarui!');
        return redirect()->route('reseller-portal.customers.index');
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->get();
        $routers = Router::active()->get();
        $odps = Odp::all();
        $availableOnus = Onu::orderBy('serial_number')
            ->take(50)
            ->get(['id', 'serial_number', 'name', 'model']);

        return view('livewire.reseller-portal.customer.edit', compact(
            'serviceProfiles',
            'routers',
            'odps',
            'availableOnus'
        ));
    }
}
