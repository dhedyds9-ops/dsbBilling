<?php

namespace App\Livewire\Isp\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\HotspotUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;

class Edit extends AdminComponent
{
    public $hotspotUserId;
    public $hotspotUser;

    // Customer
    public $name, $phone, $email, $address, $notes, $reseller_id, $latitude, $longitude;

    // Service
    public $username, $password, $service_profile_id, $billing_cycle, $setup_fee, $status, $activation_date;

    // Network
    public $router_id, $mac_address, $static_ip, $odp_id, $port_number, $onu_id, $genieacs_device_id, $network_profile_id;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->hotspotUserId = $id;
        $this->hotspotUser = HotspotUser::with('customerService.customer')->findOrFail($id);

        $cs = $this->hotspotUser->customerService;
        $cust = $cs ? $cs->customer : null;

        $this->name = $cust?->name;
        $this->phone = $cust?->phone;
        $this->email = $cust?->email;
        $this->address = $cust?->address;
        $this->latitude = $cust?->latitude;
        $this->longitude = $cust?->longitude;
        $this->reseller_id = $cust?->reseller_id;
        $this->notes = is_array($cs?->attributes) ? ($cs->attributes['notes'] ?? '') : '';

        $this->username = $this->hotspotUser->username;
        // Don't prefill password for security, let them type to change
        $this->service_profile_id = $this->hotspotUser->service_profile_id;
        $this->billing_cycle = $this->hotspotUser->billing_cycle ?? 'monthly';
        $this->setup_fee = $this->hotspotUser->setup_fee;
        $this->status = $this->hotspotUser->status;
        $this->activation_date = $this->hotspotUser->activated_at ? $this->hotspotUser->activated_at->format('Y-m-d') : '';

        $this->router_id = $this->hotspotUser->router_id;
        $this->mac_address = $this->hotspotUser->mac_address;
        $this->static_ip = $this->hotspotUser->static_ip;
        $this->odp_id = $this->hotspotUser->odp_id;
        $this->port_number = $this->hotspotUser->port_number;
        
        $this->onu_id = $cs?->onu_id;
        $this->network_profile_id = $cs?->network_profile_id;
        $this->genieacs_device_id = is_array($cs?->attributes) ? ($cs->attributes['genieacs_device_id'] ?? '') : '';
    }

    public function updateCustomer()
    {
        $this->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'reseller_id' => 'nullable|exists:users,id',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $cs = $this->hotspotUser->customerService;
        if ($cs && $cs->customer) {
            $cs->customer->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'reseller_id' => $this->reseller_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
            
            $attrs = is_array($cs->attributes) ? $cs->attributes : [];
            $attrs['notes'] = $this->notes;
            $cs->update(['attributes' => $attrs]);
        }
        
        $this->hotspotUser->update(['reseller_id' => $this->reseller_id]);
        $this->dispatch('toast', type: 'success', message: 'Profil pelanggan berhasil diperbarui!');
    }

    public function updateService()
    {
        $this->validate([
            'username' => 'required|unique:hotspot_users,username,' . $this->hotspotUserId,
            'password' => 'nullable|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'billing_cycle' => 'required|in:monthly,prepaid,postpaid',
            'setup_fee' => 'nullable|numeric',
            'status' => 'required|in:active,inactive,suspended,terminated',
            'activation_date' => 'nullable|date',
        ]);

        $this->hotspotUser->update([
            'username' => $this->username,
            'password' => $this->password ?: $this->hotspotUser->password,
            'service_profile_id' => $this->service_profile_id,
            'billing_cycle' => $this->billing_cycle,
            'setup_fee' => $this->setup_fee ?: null,
            'status' => $this->status,
            'activated_at' => $this->activation_date ? \Carbon\Carbon::parse($this->activation_date) : $this->hotspotUser->activated_at,
            'updated_by' => auth()->id(),
        ]);
        
        if ($this->hotspotUser->customerService) {
            $this->hotspotUser->customerService->update([
                'service_profile_id' => $this->service_profile_id,
                'username' => $this->username,
                'password' => $this->password ?: $this->hotspotUser->password,
                'status' => $this->status,
            ]);
        }

        $this->dispatch('toast', type: 'success', message: 'Layanan & Billing berhasil diperbarui!');
    }

    public function updateNetwork()
    {
        $this->validate([
            'router_id' => 'nullable|exists:routers,id',
            'mac_address' => 'nullable|string',
            'static_ip' => 'nullable|ip',
            'odp_id' => 'nullable|exists:odps,id',
            'port_number' => 'nullable|integer',
            'onu_id' => 'nullable|string',
            'genieacs_device_id' => 'nullable|string',
            'network_profile_id' => 'nullable|exists:network_profiles,id',
        ]);

        $this->hotspotUser->update([
            'router_id' => $this->router_id ?: null,
            'mac_address' => $this->mac_address ? strtoupper(str_replace('-', ':', $this->mac_address)) : null,
            'static_ip' => $this->static_ip ?: null,
            'odp_id' => $this->odp_id ?: null,
            'port_number' => $this->port_number ?: null,
        ]);
        
        $cs = $this->hotspotUser->customerService;
        if ($cs) {
            $cs->update([
                'network_profile_id' => $this->network_profile_id ?: null,
                'onu_id' => $this->onu_id ?: null,
            ]);
            
            $attrs = is_array($cs->attributes) ? $cs->attributes : [];
            $attrs['genieacs_device_id'] = $this->genieacs_device_id;
            $cs->update(['attributes' => $attrs]);
        }

        $this->dispatch('toast', type: 'success', message: 'Topologi Jaringan berhasil diperbarui!');
    }

    public function save()
    {
        $this->updateCustomer();
        $this->updateService();
        $this->updateNetwork();

        session()->flash('success', 'Perubahan berhasil disimpan dan disinkronisasikan ke Mikrotik.');
        return redirect()->route('isp.hotspot-users.index');
    }

    public function showUsedIps()
    {
        $used = \App\Models\ISP\HotspotUser::whereNotNull('static_ip')->pluck('static_ip')->toArray();
        $message = empty($used) ? 'Belum ada IP Static yang digunakan.' : 'IP yang sudah terpakai: ' . implode(', ', $used);
        session()->flash('info', $message);
    }

    public function render()
    {
        $customerServices = \App\Models\Customer\CustomerService::all();
        $serviceProfiles = \App\Models\ISP\ServiceProfile::where('service_type', 'hotspot')->orWhere('service_type', 'combined')->get();
        $routers = \App\Models\ISP\Router::all();
        $networkProfiles = \App\Models\Provisioning\NetworkProfile::all();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        $resellers = $userQueryService->getResellers();

        return view('livewire.isp.hotspot-user.edit', compact(
            'customerServices', 'serviceProfiles', 'routers', 'resellers', 'odps', 'onus', 'networkProfiles'
        ));
    }
}
