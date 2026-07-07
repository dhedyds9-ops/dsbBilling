<?php

namespace App\Livewire\ISP\Pop;

use App\Livewire\AdminComponent;
use App\Models\ISP\Pop as PopModel;
use App\Models\ISP\Tower;
use App\Services\ISP\PopService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $popId;
    public $pop;
    public $tower_id;
    public $code;
    public $name;
    public $description;
    public $address;
    public $province;
    public $city;
    public $district;
    public $village;
    public $latitude;
    public $longitude;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pops';
        $this->popId = $id;
        $this->pop = PopModel::findOrFail($id);

        $this->tower_id = $this->pop->tower_id;
        $this->code = $this->pop->code;
        $this->name = $this->pop->name;
        $this->description = $this->pop->description;
        $this->address = $this->pop->address;
        $this->province = $this->pop->province;
        $this->city = $this->pop->city;
        $this->district = $this->pop->district;
        $this->village = $this->pop->village;
        $this->latitude = $this->pop->latitude;
        $this->longitude = $this->pop->longitude;
        $this->status = $this->pop->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.pops.index')],
            ['label' => 'POPs', 'url' => route('isp.pops.index')],
            ['label' => $this->pop->name, 'url' => route('isp.pops.show', $this->popId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_pops,code,' . $this->popId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(PopService::class);
        $service->update($this->pop, [
            'tower_id' => $this->tower_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'village' => $this->village,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'POP berhasil diperbarui!');
        return redirect()->route('isp.pops.show', $this->popId);
    }

    public function render()
    {
        $towers = Tower::active()->get();
        return view('livewire.isp.pop.edit', compact('towers'));
    }
}
