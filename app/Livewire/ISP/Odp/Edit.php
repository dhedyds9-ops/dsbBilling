<?php

namespace App\Livewire\ISP\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp as OdpModel;
use App\Models\ISP\Odc;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $odpId;
    public $odp;
    public $odc_id;
    public $code;
    public $name;
    public $description;
    public $address;
    public $latitude;
    public $longitude;
    public $port_count;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->odpId = $id;
        $this->odp = OdpModel::findOrFail($id);

        $this->odc_id = $this->odp->odc_id;
        $this->code = $this->odp->code;
        $this->name = $this->odp->name;
        $this->description = $this->odp->description;
        $this->address = $this->odp->address;
        $this->latitude = $this->odp->latitude;
        $this->longitude = $this->odp->longitude;
        $this->port_count = $this->odp->port_count;
        $this->status = $this->odp->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => $this->odp->name, 'url' => route('isp.odps.show', $this->odpId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_odps,code,' . $this->odpId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OdpService::class);
        $service->update($this->odp, [
            'odc_id' => $this->odc_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'port_count' => $this->port_count,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'ODP berhasil diperbarui!');
        return redirect()->route('isp.odps.show', $this->odpId);
    }

    public function render()
    {
        $odcs = Odc::active()->get();
        return view('livewire.isp.odp.edit', compact('odcs'));
    }
}
