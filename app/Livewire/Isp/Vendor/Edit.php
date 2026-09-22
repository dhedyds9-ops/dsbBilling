<?php

namespace App\Livewire\Isp\Vendor;

use App\Livewire\AdminComponent;
use App\Models\ISP\Vendor as VendorModel;
use App\Services\ISP\VendorService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $vendorId;
    public $vendor;
    public $code;
    public $name;
    public $description;
    public $phone;
    public $email;
    public $address;
    public $contact_person;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vendors';
        $this->vendorId = $id;
        $this->vendor = VendorModel::findOrFail($id);

        $this->code = $this->vendor->code;
        $this->name = $this->vendor->name;
        $this->description = $this->vendor->description;
        $this->phone = $this->vendor->phone;
        $this->email = $this->vendor->email;
        $this->address = $this->vendor->address;
        $this->contact_person = $this->vendor->contact_person;
        $this->status = $this->vendor->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.vendors.index')],
            ['label' => 'Vendors', 'url' => route('isp.vendors.index')],
            ['label' => $this->vendor->name, 'url' => route('isp.vendors.show', $this->vendorId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:vendors,code,' . $this->vendorId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(VendorService::class);
        $service->update($this->vendor, [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'contact_person' => $this->contact_person,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'Vendor berhasil diperbarui!');
        return redirect()->route('isp.vendors.show', $this->vendorId);
    }

    public function render()
    {
        return view('livewire.isp.vendor.edit');
    }
}
