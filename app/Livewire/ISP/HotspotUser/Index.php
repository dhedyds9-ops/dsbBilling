<?php

namespace App\Livewire\ISP\HotspotUser;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\AAA\HotspotUser;
use App\Services\AAA\HotspotService;
use Illuminate\Support\Facades\Auth;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedIds = [];
    public bool $selectAll = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Hotspot Users'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = HotspotUser::when($this->showTrashed, fn($q) => $q->withTrashed())
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function delete($id, HotspotService $service)
    {
        $hotspotUser = HotspotUser::findOrFail($id);
        $service->terminateHotspotUser($hotspotUser->id, Auth::id());
        $hotspotUser->delete();
        session()->flash('success', 'Hotspot User berhasil dihapus!');
    }

    public function toggleStatus($id, HotspotService $service)
    {
        $hotspotUser = HotspotUser::findOrFail($id);
        if ($hotspotUser->status === 'active') {
            $service->suspendHotspotUser($hotspotUser->id, Auth::id());
        } else if ($hotspotUser->status === 'suspended') {
            $service->activateHotspotUser($hotspotUser->id, Auth::id());
        }
        session()->flash('success', 'Status Hotspot User berhasil diubah!');
    }

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->search = '';
        $this->showTrashed = false;
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function render()
    {
        $query = HotspotUser::with(['customer', 'serviceProfile'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('username', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filters['status'], function($q) {
                $q->where('status', $this->filters['status']);
            });

        $hotspotUsers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => HotspotUser::count(),
            'active' => HotspotUser::where('status', 'active')->count(),
            'inactive' => HotspotUser::where('status', 'inactive')->count(),
            'online' => HotspotUser::where('status', 'active')->where('is_online', true)->count(),
        ];

        return view('livewire.isp.hotspot-user.index', compact('hotspotUsers', 'stats'));
    }
}
