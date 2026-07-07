<?php

namespace App\Livewire\ISP\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Services\ISP\OnuService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $olt_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $mac_address;
    public $pon_port;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'onus';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.onus.index')],
            ['label' => 'ONU', 'url' => route('isp.onus.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_onus,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OnuService::class);
        $service->create([
            'olt_id' => $this->olt_id,
            'vendor_id' => $this->vendor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'mac_address' => $this->mac_address,
            'pon_port' => $this->pon_port,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'ONU berhasil dibuat!');
        return redirect()->route('isp.onus.index');
    }

    public function render()
    {
        $olts = Olt::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.onu.create', compact('olts', 'vendors'));
    }
}
