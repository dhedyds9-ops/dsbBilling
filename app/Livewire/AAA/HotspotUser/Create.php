<?php

namespace App\Livewire\AAA\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\AAA\HotspotUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\AAA\VoucherPool;

class Create extends AdminComponent
{
    public $username;
    public $password;
    public $customer_service_id;
    public $service_profile_id;
    public $voucher_pool_id;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'hotspot-users';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.hotspot-users.index')],
            ['label' => 'Hotspot Users', 'url' => route('aaa.hotspot-users.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'username' => 'required|unique:hotspot_users,username',
            'password' => 'required',
            'status' => 'required|in:active,inactive,suspended,terminated',
        ]);

        HotspotUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => $this->username,
            'password' => $this->password,
            'customer_service_id' => $this->customer_service_id,
            'service_profile_id' => $this->service_profile_id,
            'voucher_pool_id' => $this->voucher_pool_id,
            'status' => $this->status,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Hotspot User berhasil dibuat!');
        return redirect()->route('aaa.hotspot-users.index');
    }

    public function render()
    {
        $customerServices = CustomerService::all();
        $serviceProfiles = ServiceProfile::all();
        $voucherPools = VoucherPool::all();
        return view('livewire.aaa.hotspot-user.create', compact('customerServices', 'serviceProfiles', 'voucherPools'));
    }
}
