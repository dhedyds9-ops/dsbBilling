<?php

namespace App\Livewire\Isp\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
    public $olt_id;
    public $pop_id;
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
        $this->activePage = 'odcs';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:odcs,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OdcService::class);
        $service->create([
            'olt_id' => $this->olt_id,
            'pop_id' => $this->pop_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'port_count' => $this->port_count,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'ODC berhasil dibuat!');
        return redirect()->route('isp.odcs.index');
    }

    public function render()
    {
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        return view('livewire.isp.odc.create', compact('olts', 'pops'));
    }
}
