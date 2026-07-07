<?php

namespace App\Livewire\AAA\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\AAA\HotspotUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\AAA\VoucherPool;

class Edit extends AdminComponent
{
    public $hotspotUserId;
    public $hotspotUser;
    public $username;
    public $password;
    public $customer_service_id;
    public $service_profile_id;
    public $voucher_pool_id;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'hotspot-users';
        $this->hotspotUserId = $id;
        $this->hotspotUser = HotspotUser::findOrFail($id);

        $this->username = $this->hotspotUser->username;
        $this->password = $this->hotspotUser->password;
        $this->customer_service_id = $this->hotspotUser->customer_service_id;
        $this->service_profile_id = $this->hotspotUser->service_profile_id;
        $this->voucher_pool_id = $this->hotspotUser->voucher_pool_id;
        $this->status = $this->hotspotUser->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.hotspot-users.index')],
            ['label' => 'Hotspot Users', 'url' => route('aaa.hotspot-users.index')],
            ['label' => $this->hotspotUser->username, 'url' => route('aaa.hotspot-users.show', $this->hotspotUserId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'username' => 'required|unique:hotspot_users,username,' . $this->hotspotUserId,
            'password' => 'required',
            'status' => 'required|in:active,inactive,suspended,terminated',
        ]);

        $this->hotspotUser->update([
            'username' => $this->username,
            'password' => $this->password,
            'customer_service_id' => $this->customer_service_id,
            'service_profile_id' => $this->service_profile_id,
            'voucher_pool_id' => $this->voucher_pool_id,
            'status' => $this->status,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Hotspot User berhasil diperbarui!');
        return redirect()->route('aaa.hotspot-users.show', $this->hotspotUserId);
    }

    public function render()
    {
        $customerServices = CustomerService::all();
        $serviceProfiles = ServiceProfile::all();
        $voucherPools = VoucherPool::all();
        return view('livewire.aaa.hotspot-user.edit', compact('customerServices', 'serviceProfiles', 'voucherPools'));
    }
}
