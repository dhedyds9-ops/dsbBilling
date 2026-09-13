<?php

namespace App\Livewire\ISP\NetworkProfile;

use App\Livewire\AdminComponent;
use App\Models\Provisioning\NetworkProfile;

class Index extends AdminComponent
{
    public $search = '';

    protected $queryString = ['search'];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'network-profiles';
        
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Jaringan', 'url' => '#'],
            ['label' => 'Network Profile'],
        ];
    }

    public function render()
    {
        $profiles = NetworkProfile::with('router')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.isp.network-profile.index', [
            'profiles' => $profiles,
        ])->layout('layouts.enterprise');
    }

    public function delete($id)
    {
        $profile = NetworkProfile::findOrFail($id);
        
        // Cek jika sedang digunakan
        if ($profile->vlan_id && \App\Models\Customer\CustomerService::where('network_profile_id', $id)->exists()) {
            session()->flash('error', 'Profile sedang digunakan oleh pelanggan.');
            return;
        }

        $profile->delete();
        session()->flash('success', 'Network Profile berhasil dihapus.');
    }
}
