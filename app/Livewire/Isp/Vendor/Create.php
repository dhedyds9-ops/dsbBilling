<?php

namespace App\Livewire\Isp\Vendor;

use App\Livewire\AdminComponent;
use App\Services\ISP\VendorService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $code;
    public $name;
    public $contact_person;
    public $phone;
    public $email;
    public $address;
    public $description;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vendors';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.vendors.index')],
            ['label' => 'Vendors', 'url' => route('isp.vendors.index')],
            ['label' => 'Create'],
        ];
    }

    
    public function updatedName($value)
    {
        if (empty($this->code) && !empty($value)) {
            // e.g. "Huawei Tech" -> "HUAWEI-TECH"
            $this->code = strtoupper(\Illuminate\Support\Str::slug($value));
        }
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:vendors,code',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(VendorService::class);
        $service->create([
            'code' => $this->code,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'description' => $this->description,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'Vendor berhasil dibuat!');
        return redirect()->route('isp.vendors.index');
    }

    public function render()
    {
        return view('livewire.isp.vendor.create');
    }
}
