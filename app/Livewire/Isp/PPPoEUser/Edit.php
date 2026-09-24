<?php

namespace App\Livewire\Isp\PPPoEUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\PPPoEUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;

class Edit extends AdminComponent
{
    public $pppoeUserId;
    public $pppoeUser;

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
        $this->activePage = 'pppoe-users';
        $this->pppoeUserId = $id;
        $this->pppoeUser = PPPoEUser::with('customerService.customer')->findOrFail($id);

        $cs = $this->pppoeUser->customerService;
        $cust = $cs ? $cs->customer : null;

        $this->name = $cust?->name;
        $this->phone = $cust?->phone;
        $this->email = $cust?->email;
        $this->address = $cust?->address;
        $this->latitude = $cust?->latitude;
        $this->longitude = $cust?->longitude;
        $this->reseller_id = $cust?->reseller_id;
        $this->notes = is_array($cs?->attributes) ? ($cs->attributes['notes'] ?? '') : '';

        $this->username = $this->pppoeUser->username;
        $this->password = $this->pppoeUser->password;
        $this->service_profile_id = $this->pppoeUser->service_profile_id;
        $this->billing_cycle = $this->pppoeUser->billing_cycle ?? 'monthly';
        $this->setup_fee = $this->pppoeUser->setup_fee;
        $this->status = $this->pppoeUser->status;
        $this->activation_date = $this->pppoeUser->activated_at ? $this->pppoeUser->activated_at->format('Y-m-d') : '';

        $this->router_id = $this->pppoeUser->router_id;
        $this->mac_address = $this->pppoeUser->mac_address;
        $this->static_ip = $this->pppoeUser->static_ip;
        $this->odp_id = $this->pppoeUser->odp_id;
        $this->port_number = $this->pppoeUser->port_number;
        
        $this->onu_id = $cs?->onu?->serial_number ?? $cs?->onu_id;
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

        $cs = $this->pppoeUser->customerService;
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
        
        $this->pppoeUser->update(['reseller_id' => $this->reseller_id]);
        $this->dispatch('toast', type: 'success', message: 'Profil pelanggan berhasil diperbarui!');
    }

    public function updateService()
    {
        $this->validate([
            'username' => 'required|unique:pppoe_users,username,' . $this->pppoeUserId,
            'password' => 'nullable|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'billing_cycle' => 'required|in:monthly,prepaid,postpaid',
            'setup_fee' => 'nullable|numeric',
            'status' => 'required|in:active,inactive,suspended,terminated',
            'activation_date' => 'nullable|date',
        ]);

        $this->pppoeUser->update([
            'username' => $this->username,
            'password' => $this->password,
            'service_profile_id' => $this->service_profile_id,
            'billing_cycle' => $this->billing_cycle,
            'setup_fee' => $this->setup_fee ?: null,
            'status' => $this->status,
            'activated_at' => $this->activation_date ? \Carbon\Carbon::parse($this->activation_date) : $this->pppoeUser->activated_at,
            'updated_by' => auth()->id(),
        ]);
        
        if ($this->pppoeUser->customerService) {
            $this->pppoeUser->customerService->update([
                'service_profile_id' => $this->service_profile_id,
                'username' => $this->username,
                'password' => $this->password,
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
            'static_ip' => 'nullable|ip|unique:pppoe_users,static_ip,' . $this->pppoeUserId,
            'odp_id' => 'nullable|exists:odps,id',
            'port_number' => 'nullable|integer',
            'onu_id' => 'nullable|string',
            'genieacs_device_id' => 'nullable|string',
            'network_profile_id' => 'nullable|exists:network_profiles,id',
        ]);

        $this->pppoeUser->update([
            'router_id' => $this->router_id ?: null,
            'mac_address' => $this->mac_address ? strtoupper(str_replace('-', ':', $this->mac_address)) : null,
            'static_ip' => $this->static_ip ?: null,
            'odp_id' => $this->odp_id ?: null,
            'port_number' => $this->port_number ?: null,
        ]);
        
        $cs = $this->pppoeUser->customerService;
        if ($cs) {
            $actualOnuId = null;
            if ($this->onu_id) {
                $onu = \App\Models\ISP\Onu::where('serial_number', $this->onu_id)->first();
                if ($onu) {
                    $actualOnuId = $onu->id;
                } elseif (is_numeric($this->onu_id)) {
                    $actualOnuId = $this->onu_id;
                } else {
                    $this->addError('onu_id', 'Serial Number tidak ditemukan di database ONU.');
                    return;
                }
            }

            $cs->update([
                'network_profile_id' => $this->network_profile_id ?: null,
                'onu_id' => $actualOnuId,
            ]);
            
            $attrs = is_array($cs->attributes) ? $cs->attributes : [];
            $attrs['genieacs_device_id'] = $this->genieacs_device_id;
            $cs->update(['attributes' => $attrs]);
        }

        $this->dispatch('toast', type: 'success', message: 'Topologi Jaringan berhasil diperbarui!');
    }

    public function save()
    {
        // Execute all updates sequentially
        $this->updateCustomer();
        $this->updateService();
        $this->updateNetwork();
        
        session()->flash('success', 'Perubahan pada PPPoE User berhasil disimpan!');
        return redirect()->route('isp.pppoe-users.index');
    }

    public function showUsedIps()
    {
        $used = \App\Models\ISP\PPPoEUser::whereNotNull('static_ip')->pluck('static_ip')->toArray();
        $message = empty($used) ? 'Belum ada IP Static yang digunakan.' : 'IP yang sudah terpakai: ' . implode(', ', $used);
        session()->flash('info', $message);
    }

    public function render()
    {
        $customerServices = \App\Models\Customer\CustomerService::all();
        $serviceProfiles = \App\Models\ISP\ServiceProfile::all();
        $routers = \App\Models\ISP\Router::active()->get();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        $resellers = $userQueryService->getResellers();

        return view('livewire.isp.pppoe-user.edit', compact('customerServices', 'serviceProfiles', 'routers', 'odps', 'onus', 'resellers'));
    }
}
