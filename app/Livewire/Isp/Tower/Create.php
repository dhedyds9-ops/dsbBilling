<?php

namespace App\Livewire\Isp\Tower;

use App\Livewire\AdminComponent;
use App\Services\ISP\TowerService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
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
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'towers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.towers.index')],
            ['label' => 'Towers', 'url' => route('isp.towers.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:towers,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(TowerService::class);
        $service->create([
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

        session()->flash('success', 'Tower berhasil dibuat!');
        return redirect()->route('isp.towers.index');
    }

    public function render()
    {
        return view('livewire.isp.tower.create');
    }
}
