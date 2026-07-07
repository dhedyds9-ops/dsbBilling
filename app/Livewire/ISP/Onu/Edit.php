<?php

namespace App\Livewire\ISP\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu as OnuModel;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Services\ISP\OnuService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $onuId;
    public $onu;
    public $olt_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $mac_address;
    public $pon_port;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'onus';
        $this->onuId = $id;
        $this->onu = OnuModel::findOrFail($id);

        $this->olt_id = $this->onu->olt_id;
        $this->vendor_id = $this->onu->vendor_id;
        $this->code = $this->onu->code;
        $this->name = $this->onu->name;
        $this->description = $this->onu->description;
        $this->model = $this->onu->model;
        $this->serial_number = $this->onu->serial_number;
        $this->mac_address = $this->onu->mac_address;
        $this->pon_port = $this->onu->pon_port;
        $this->status = $this->onu->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.onus.index')],
            ['label' => 'ONU', 'url' => route('isp.onus.index')],
            ['label' => $this->onu->name, 'url' => route('isp.onus.show', $this->onuId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_onus,code,' . $this->onuId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OnuService::class);
        $service->update($this->onu, [
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

        session()->flash('success', 'ONU berhasil diperbarui!');
        return redirect()->route('isp.onus.show', $this->onuId);
    }

    public function render()
    {
        $olts = Olt::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.onu.edit', compact('olts', 'vendors'));
    }
}
