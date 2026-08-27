<?php

namespace App\Livewire\ISP\PPPoEUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\PPPoEUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;

class Edit extends AdminComponent
{
    public $pppoeUserId;
    public $pppoeUser;
    public $username;
    public $password;
    public $customer_service_id;
    public $service_profile_id;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pppoe-users';
        $this->pppoeUserId = $id;
        $this->pppoeUser = PPPoEUser::findOrFail($id);

        $this->username = $this->pppoeUser->username;
        $this->password = $this->pppoeUser->password;
        $this->customer_service_id = $this->pppoeUser->customer_service_id;
        $this->service_profile_id = $this->pppoeUser->service_profile_id;
        $this->status = $this->pppoeUser->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.pppoe-users.index')],
            ['label' => 'PPPoE Users', 'url' => route('isp.pppoe-users.index')],
            ['label' => $this->pppoeUser->username, 'url' => route('isp.pppoe-users.show', $this->pppoeUserId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'username' => 'required|unique:pppoe_users,username,' . $this->pppoeUserId,
            'password' => 'required',
            'status' => 'required|in:active,inactive,suspended,terminated',
        ]);

        $this->pppoeUser->update([
            'username' => $this->username,
            'password' => $this->password,
            'customer_service_id' => $this->customer_service_id,
            'service_profile_id' => $this->service_profile_id,
            'status' => $this->status,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'PPPoE User berhasil diperbarui!');
        return redirect()->route('isp.pppoe-users.show', $this->pppoeUserId);
    }

    public function render()
    {
        $customerServices = CustomerService::all();
        $serviceProfiles = ServiceProfile::all();
        return view('livewire.isp.pppoe-user.edit', compact('customerServices', 'serviceProfiles'));
    }
}
