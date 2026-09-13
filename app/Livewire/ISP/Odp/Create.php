<?php

namespace App\Livewire\ISP\Odp;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $odc_id;
    public $code;
    public $name;
    public $description;
    public $address;
    public $latitude;
    public $longitude;
    public $port_count;
    public $status = 'active';

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
            'code' => 'required|unique:odps,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OdpService::class);
        $service->create([
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

        session()->flash('success', 'ODP berhasil dibuat!');
        return redirect()->route('isp.odps.index');
    }

    public function render()
    {
        $odcs = Odc::active()->get();
        return view('livewire.isp.odp.create', compact('odcs'));
    }
}
