<?php

namespace App\Livewire\ISP\PppoeUser;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\AAA\PPPoEUser;
use App\Services\AAA\PPPoEService;
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
        $this->activePage = 'pppoe-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'PPPoE Users'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = PPPoEUser::when($this->showTrashed, fn($q) => $q->withTrashed())
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function delete($id, PPPoEService $service)
    {
        $pppoeUser = PPPoEUser::findOrFail($id);
        $service->terminatePPPoEUser($pppoeUser->id, Auth::id());
        $pppoeUser->delete();
        session()->flash('success', 'PPPoE User berhasil dihapus!');
    }

    public function toggleStatus($id, PPPoEService $service)
    {
        $pppoeUser = PPPoEUser::findOrFail($id);
        if ($pppoeUser->status === 'active') {
            $service->suspendPPPoEUser($pppoeUser->id, Auth::id());
        } else if ($pppoeUser->status === 'suspended') {
            $service->reactivatePPPoEUser($pppoeUser->id, Auth::id());
        }
        session()->flash('success', 'Status PPPoE User berhasil diubah!');
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
        $query = PPPoEUser::with(['customer', 'serviceProfile'])
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

        $pppoeUsers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => PPPoEUser::count(),
            'active' => PPPoEUser::where('status', 'active')->count(),
            'inactive' => PPPoEUser::where('status', 'inactive')->count(),
            'online' => PPPoEUser::where('status', 'active')->where('is_online', true)->count(),
        ];

        return view('livewire.isp.pppoe-user.index', compact('pppoeUsers', 'stats'));
    }
}
