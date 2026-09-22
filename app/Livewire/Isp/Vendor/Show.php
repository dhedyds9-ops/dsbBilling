<?php

namespace App\Livewire\ISP\Vendor;

use App\Livewire\AdminComponent;
use App\Models\ISP\Vendor as VendorModel;

class Show extends AdminComponent
{
    public $vendorId;
    public $vendor;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vendors';
        $this->vendorId = $id;
        $this->vendor = VendorModel::with(['olts', 'splitters', 'onus', 'routers', 'nasDevices'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.vendors.index')],
            ['label' => 'Vendors', 'url' => route('isp.vendors.index')],
            ['label' => $this->vendor->name],
        ];
    }

    public function render()
    {
        return view('livewire.isp.vendor.show');
    }
}
