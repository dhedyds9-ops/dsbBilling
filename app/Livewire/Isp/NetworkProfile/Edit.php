<?php

namespace App\Livewire\ISP\NetworkProfile;

use App\Livewire\AdminComponent;
use App\Models\Provisioning\NetworkProfile;
use App\Models\ISP\Router;

class Edit extends AdminComponent
{
    public $profileId;
    public $name;
    public $description;
    public $type;
    public $router_id;
    public $vlan_id;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'network-profiles';
        
        $profile = NetworkProfile::findOrFail($id);
        $this->profileId = $profile->id;
        $this->name = $profile->name;
        $this->description = $profile->description;
        $this->type = $profile->type;
        $this->router_id = $profile->router_id;
        $this->vlan_id = $profile->vlan_id;

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network Profile', 'url' => route('isp.network-profiles.index')],
            ['label' => 'Edit'],
        ];
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:network_profiles,name,' . $this->profileId,
            'description' => 'nullable|string',
            'type' => 'required|in:pppoe,hotspot,static',
            'router_id' => 'required|exists:routers,id',
            'vlan_id' => 'required|integer|min:1|max:4094',
        ];
    }

    public function save()
    {
        $this->validate();

        $profile = NetworkProfile::findOrFail($this->profileId);
        $profile->update([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'router_id' => $this->router_id,
            'vlan_id' => $this->vlan_id,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Network Profile berhasil diupdate.');
        return redirect()->route('isp.network-profiles.index');
    }

    public function render()
    {
        return view('livewire.isp.network-profile.edit', [
            'routers' => Router::all(),
        ])->layout('layouts.enterprise');
    }
}

