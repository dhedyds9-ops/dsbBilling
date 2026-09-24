<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Edit extends AdminComponent
{
    public $customerId;
    public $customer;
    
    // basic fields that might exist in blade
    public $name = '';
    public $customer_code = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $latitude = '';
    public $longitude = '';
    public $status = '';
    public $service_profile_id;
    public $router_id;
    public $pppoe_username = '';
    public $pppoe_password = '';
    public $reseller_id = '';
    public $branch_id = '';
    public $notes = '';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $this->customer = Customer::findOrFail($id);
        
        $this->name = $this->customer->name;
        $this->customer_code = $this->customer->customer_code;
        $this->email = $this->customer->email;
        $this->phone = $this->customer->phone;
        $this->address = $this->customer->address;
        $this->latitude = $this->customer->latitude;
        $this->longitude = $this->customer->longitude;
        $this->status = $this->customer->status;
        $this->reseller_id = $this->customer->reseller_id;
        $this->branch_id = $this->customer->branch_id;
        $this->notes = $this->customer->notes;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'reseller_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($this->customerId);
        $oldResellerId = $customer->reseller_id;
        $newResellerId = $this->reseller_id ?: null;

        $customer->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'reseller_id' => $newResellerId,
            'notes' => $this->notes,
            'updated_by' => auth()->id(),
        ]);

        // Cascade reseller_id changes to all child services if changed
        if ($oldResellerId !== $newResellerId) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($customer, $newResellerId) {
                // Get all services bypassing branch scope (since Admin is running this)
                $services = \App\Models\Customer\CustomerService::withoutGlobalScope('branch_isolation')
                    ->where('customer_id', $customer->id)
                    ->get();
                
                foreach ($services as $service) {
                    $service->update(['reseller_id' => $newResellerId]);
                    
                    // Update PPPoE User
                    $pppoe = \App\Models\ISP\PPPoEUser::withoutGlobalScope('branch_isolation')
                        ->where('customer_service_id', $service->id)
                        ->first();
                    if ($pppoe) {
                        $pppoe->update(['reseller_id' => $newResellerId]);
                    }

                    // Update Hotspot User
                    $hotspot = \App\Models\ISP\HotspotUser::withoutGlobalScope('branch_isolation')
                        ->where('customer_service_id', $service->id)
                        ->first();
                    if ($hotspot) {
                        $hotspot->update(['reseller_id' => $newResellerId]);
                    }
                }
            });
        }

        session()->flash('success', 'Customer berhasil diperbarui!');
        return redirect()->route('crm.customers.index');
    }

    public function render()
    {
        $resellers = app(\App\Services\Auth\UserQueryService::class)->getResellers();
        return view('livewire.crm.customer.edit', compact('resellers'));
    }
}
