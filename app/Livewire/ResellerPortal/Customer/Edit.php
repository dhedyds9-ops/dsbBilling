<?php
namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\ISP\Onu;

class Edit extends AdminComponent
{
    public $customerId;
    public $name;
    public $phone;
    public $email;
    public $status = 'active';
    public $onu_serial_number = '';
    public $address;
    public $latitude;
    public $longitude;
    public $notes;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        
        $ownerId = auth()->user()->getEffectiveResellerId();
        $customer = Customer::with(['customerServices.onu', 'customerServices.acsDevice'])
            ->where(function($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            })->findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->status = $customer->status ?? 'active';
        $this->onu_serial_number = $customer->onu_device['sn'] ?? '';
        $this->address = $customer->address;
        $this->latitude = $customer->latitude;
        $this->longitude = $customer->longitude;
        $this->notes = $customer->notes;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => route('reseller-portal.customers.index')],
            ['label' => 'Edit ' . $customer->name],
        ];
    }

    public function save()
    {
        return $this->update();
    }

    public function update()
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,suspended,suspend',
            'onu_serial_number' => 'nullable|string|max:100',
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

        $customer->update([
            'name'      => $this->name,
            'phone'     => $this->phone,
            'email'     => $this->email,
            'status'    => $this->status,
            'address'   => $this->address,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'notes'     => $this->notes,
        ]);

        // Manage ONU Serial Number association
        $actualOnuId = null;
        if (!empty(trim((string)$this->onu_serial_number))) {
            $cleanSn = strtoupper(trim($this->onu_serial_number));
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

        $service = $customer->customerServices()->first();
        if ($service) {
            $service->update(['onu_id' => $actualOnuId]);
        } elseif ($actualOnuId) {
            $customer->customerServices()->create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'onu_id' => $actualOnuId,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
        }

        session()->flash('success', 'Data pelanggan dan ONU berhasil diperbarui!');
        return redirect()->route('reseller-portal.customers.index');
    }

    public function render()
    {
        $availableOnus = Onu::orderBy('serial_number')
            ->take(50)
            ->get(['id', 'serial_number', 'name', 'model']);

        return view('livewire.reseller-portal.customer.edit', compact('availableOnus'));
    }
}
