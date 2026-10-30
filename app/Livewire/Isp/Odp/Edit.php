<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odp as OdpModel;
use App\Models\ISP\Odc;
use App\Models\Master\Branch;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $odpId;
    public $odp;
    public $branch_id;
    public $odc_id;
    public $code;
    public $name;
    public $description;
    public $address;
    public $province;
    public $regency;
    public $district;
    public $village;
    public $postal_code;
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

        $this->branch_id = $this->odp->branch_id;
        $this->odc_id = $this->odp->odc_id;
        $this->code = $this->odp->code;
        $this->name = $this->odp->name;
        $this->description = $this->odp->description;
        $this->address = $this->odp->address;
        $this->province = $this->odp->province;
        $this->regency = $this->odp->regency;
        $this->district = $this->odp->district;
        $this->village = $this->odp->village;
        $this->postal_code = $this->odp->postal_code;
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
            'branch_id' => 'nullable|exists:branches,id',
            'code' => 'required|unique:odps,code,' . $this->odpId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'regency' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $service = app(OdpService::class);
        $service->update($this->odp, [
            'branch_id' => $this->branch_id === '' ? null : $this->branch_id,
            'odc_id' => $this->odc_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'regency' => $this->regency,
            'district' => $this->district,
            'village' => $this->village,
            'postal_code' => $this->postal_code,
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
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $odcs = Odc::active()->get();
        return view('livewire.isp.odp.edit', compact('branches', 'odcs'));
    }
}
