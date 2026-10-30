<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc;
use App\Models\Master\Branch;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
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
    public $status = 'active';

    public function updatedOdcId($odcId)
    {
        if ($odcId) {
            $odc = Odc::find($odcId);
            if ($odc && $odc->branch_id && empty($this->branch_id)) {
                $this->branch_id = $odc->branch_id;
            }
        }
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'code' => 'required|unique:odps,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'regency' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $service = app(OdpService::class);
        $service->create([
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

        session()->flash('success', 'ODP berhasil dibuat!');
        return redirect()->route('isp.odps.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $odcs = Odc::active()->get();
        return view('livewire.isp.odp.create', compact('branches', 'odcs'));
    }
}
