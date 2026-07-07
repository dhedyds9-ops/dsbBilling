<?php

namespace App\Livewire\ACS\Device;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\Asset;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Odp;
use App\Models\ISP\Vendor;

class Edit extends BaseACSComponent
{
    public $deviceId;
    public $device;

    public $serial_number;
    public $mac_address;
    public $oui;
    public $manufacturer;
    public $vendor_id;
    public $model;
    public $product_class;
    public $hardware_version;
    public $software_version;
    public $firmware_version;
    public $ip_address;
    public $connection_request_url;
    public $status;
    public $customer_service_id;
    public $asset_id;
    public $onu_id;
    public $olt_id;
    public $pop_id;
    public $odp_id;
    public $latitude;
    public $longitude;
    public $signal;
    public $uptime;
    public $cpu;
    public $memory;
    public $temperature;
    public $notes;

    public function mount($id = null)
    {
        parent::mount();
        $this->deviceId = $id;
        $this->device = ACSDevice::findOrFail($id);
        $this->fill($this->device->toArray());
        $this->activeModule = 'acs';
        $this->activePage = 'devices';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Devices', 'url' => route('acs.devices.index')],
            ['label' => $this->device->serial_number, 'url' => route('acs.devices.show', $id)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $validated = $this->validate([
            'serial_number' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'status' => 'required|string|in:online,offline,unknown',
        ]);

        $validated['updated_by'] = auth()->id();

        $this->device->update($validated);

        session()->flash('success', 'Device berhasil diperbarui!');
        return redirect()->route('acs.devices.show', $this->deviceId);
    }

    public function render()
    {
        $vendors = Vendor::all();
        $customerServices = CustomerService::all();
        $assets = Asset::all();
        $onus = Onu::all();
        $olts = Olt::all();
        $pops = Pop::all();
        $odps = Odp::all();

        return view('livewire.acs.device.edit', compact('vendors', 'customerServices', 'assets', 'onus', 'olts', 'pops', 'odps'));
    }
}
