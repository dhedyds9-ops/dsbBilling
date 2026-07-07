<?php

namespace App\Livewire\AAA\HotspotUser;

use App\Livewire\AAA\BaseAAAComponent;
use App\Models\AAA\HotspotUser;
use App\Models\Customer;

class Index extends BaseAAAComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'hotspot-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.hotspot-users.index')],
            ['label' => 'Hotspot Users'],
        ];
    }

    public function delete($id)
    {
        $hotspotUser = HotspotUser::findOrFail($id);
        $hotspotUser->delete();
        session()->flash('success', 'Hotspot User berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = HotspotUser::query()->with(['customer', 'serviceProfile']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $hotspotUsers = $query->orderBy($this->sortField, $this->sortDirection)
                            ->paginate($this->perPage);

        $stats = [
            'total' => HotspotUser::count(),
            'active' => HotspotUser::where('status', 'active')->count(),
            'inactive' => HotspotUser::where('status', 'inactive')->count(),
            'online' => HotspotUser::where('status', 'active')->where('is_online', true)->count(),
        ];

        return view('livewire.aaa.hotspot-user.index', compact('hotspotUsers', 'stats'));
    }
}
