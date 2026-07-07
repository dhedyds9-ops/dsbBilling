<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\AdminComponent;
use App\Models\ISP\ServiceProfile;
use App\Services\AAA\VoucherService;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $service_profile_id;
    public $quantity = 10;
    public $prefix;
    public $length = 8;
    public $validity_days;
    public $notes;

    public $generated_vouchers = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vouchers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('isp.vouchers.index')],
            ['label' => 'Generate Voucher'],
        ];
    }

    public function generate(VoucherService $service)
    {
        $this->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'length' => 'required|integer|min:4|max:32',
            'validity_days' => 'nullable|integer|min:1',
        ]);

        // First create a voucher pool
        $serviceProfile = ServiceProfile::find($this->service_profile_id);
        
        // Check if we need to use a pool - for now we'll create directly
        // For simplicity, let's create vouchers directly linked to service profile
        $vouchers = [];
        
        for ($i = 0; $i < $this->quantity; $i++) {
            $code = ($this->prefix ?: '') . strtoupper(Str::random($this->length));
            
            $voucher = \App\Models\AAA\Voucher::create([
                'code' => $code,
                'service_profile_id' => $this->service_profile_id,
                'validity_days' => $this->validity_days,
                'status' => 'available',
                'notes' => $this->notes,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            
            $vouchers[] = $voucher;
        }

        $this->generated_vouchers = $vouchers;
        session()->flash('success', 'Voucher berhasil di-generate!');
    }

    public function resetForm()
    {
        $this->service_profile_id = null;
        $this->quantity = 10;
        $this->prefix = null;
        $this->length = 8;
        $this->validity_days = null;
        $this->notes = null;
        $this->generated_vouchers = [];
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->where('service_type', 'voucher')->orWhere('service_type', 'hotspot')->orWhere('service_type', 'combined')->get();
        return view('livewire.isp.voucher.create', compact('serviceProfiles'));
    }
}
