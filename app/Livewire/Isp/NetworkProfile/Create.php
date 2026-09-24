<?php

namespace App\Livewire\Isp\NetworkProfile;

use App\Livewire\AdminComponent;
use App\Models\Provisioning\NetworkProfile;
use App\Models\ISP\Router;

class Create extends AdminComponent
{
    public $name;
    public $description;
    public $type = 'pppoe';
    public $router_id;
    public $vlan_id;

    protected $rules = [
        'name' => 'required|string|max:255|unique:network_profiles,name',
        'description' => 'nullable|string',
        'type' => 'required|in:pppoe,hotspot,static',
        'router_id' => 'required|exists:routers,id',
        'vlan_id' => 'required|integer|min:1|max:4094',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'network-profiles';
        
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network Profile', 'url' => route('isp.network-profiles.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate();

        NetworkProfile::create([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'router_id' => $this->router_id,
            'vlan_id' => $this->vlan_id,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Network Profile berhasil dibuat.');
        return redirect()->route('isp.network-profiles.index');
    }

    public function render()
    {
        return view('livewire.isp.network-profile.create', [
            'routers' => Router::all(),
        ])->layout('layouts.enterprise');
    }
}
