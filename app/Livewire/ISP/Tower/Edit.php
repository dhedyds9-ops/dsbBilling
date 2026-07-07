<?php

namespace App\Livewire\ISP\Tower;

use App\Livewire\AdminComponent;
use App\Models\ISP\Tower as TowerModel;
use App\Services\ISP\TowerService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $towerId;
    public $tower;
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
    public $height;
    public $type;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'towers';
        $this->towerId = $id;
        $this->tower = TowerModel::findOrFail($id);

        $this->code = $this->tower->code;
        $this->name = $this->tower->name;
        $this->description = $this->tower->description;
        $this->address = $this->tower->address;
        $this->province = $this->tower->province;
        $this->city = $this->tower->city;
        $this->district = $this->tower->district;
        $this->village = $this->tower->village;
        $this->latitude = $this->tower->latitude;
        $this->longitude = $this->tower->longitude;
        $this->height = $this->tower->height;
        $this->type = $this->tower->type;
        $this->status = $this->tower->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.towers.index')],
            ['label' => 'Towers', 'url' => route('isp.towers.index')],
            ['label' => $this->tower->name, 'url' => route('isp.towers.show', $this->towerId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_towers,code,' . $this->towerId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(TowerService::class);
        $service->update($this->tower, [
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
            'height' => $this->height,
            'type' => $this->type,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'Tower berhasil diperbarui!');
        return redirect()->route('isp.towers.show', $this->towerId);
    }

    public function render()
    {
        return view('livewire.isp.tower.edit');
    }
}
