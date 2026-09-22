<?php

namespace App\Livewire\ISP\Odc;

use App\Livewire\AdminComponent;
use App\Models\ISP\Odc as OdcModel;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $odcId;
    public $odc;
    public $olt_id;
    public $pop_id;
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
        $this->activePage = 'odcs';
        $this->odcId = $id;
        $this->odc = OdcModel::findOrFail($id);

        $this->olt_id = $this->odc->olt_id;
        $this->pop_id = $this->odc->pop_id;
        $this->code = $this->odc->code;
        $this->name = $this->odc->name;
        $this->description = $this->odc->description;
        $this->address = $this->odc->address;
        $this->latitude = $this->odc->latitude;
        $this->longitude = $this->odc->longitude;
        $this->port_count = $this->odc->port_count;
        $this->status = $this->odc->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odcs.index')],
            ['label' => 'ODC', 'url' => route('isp.odcs.index')],
            ['label' => $this->odc->name, 'url' => route('isp.odcs.show', $this->odcId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:odcs,code,' . $this->odcId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OdcService::class);
        $service->update($this->odc, [
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

        session()->flash('success', 'ODC berhasil diperbarui!');
        return redirect()->route('isp.odcs.show', $this->odcId);
    }

    public function render()
    {
        $olts = Olt::active()->get();
        $pops = Pop::active()->get();
        return view('livewire.isp.odc.edit', compact('olts', 'pops'));
    }
}
