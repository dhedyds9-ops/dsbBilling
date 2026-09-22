<?php

namespace App\Livewire\ISP\Pop;

use App\Livewire\AdminComponent;
use App\Models\ISP\Tower;
use App\Services\ISP\PopService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
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
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pops';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.pops.index')],
            ['label' => 'POPs', 'url' => route('isp.pops.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:pops,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(PopService::class);
        $service->create([
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

        session()->flash('success', 'POP berhasil dibuat!');
        return redirect()->route('isp.pops.index');
    }

    public function render()
    {
        $towers = Tower::active()->get();
        return view('livewire.isp.pop.create', compact('towers'));
    }
}
