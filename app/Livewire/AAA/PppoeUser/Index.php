<?php

namespace App\Livewire\AAA\PppoeUser;

use App\Livewire\AAA\BaseAAAComponent;
use App\Models\AAA\PPPoEUser;
use App\Models\Customer;

class Index extends BaseAAAComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'pppoe-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.pppoe-users.index')],
            ['label' => 'PPPoE Users'],
        ];
    }

    public function delete($id)
    {
        $pppoeUser = PPPoEUser::findOrFail($id);
        $pppoeUser->delete();
        session()->flash('success', 'PPPoE User berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = PPPoEUser::query()->with(['customer', 'serviceProfile']);

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

        $pppoeUsers = $query->orderBy($this->sortField, $this->sortDirection)
                          ->paginate($this->perPage);

        $stats = [
            'total' => PPPoEUser::count(),
            'active' => PPPoEUser::where('status', 'active')->count(),
            'inactive' => PPPoEUser::where('status', 'inactive')->count(),
            'online' => PPPoEUser::where('status', 'active')->where('is_online', true)->count(),
        ];

        return view('livewire.aaa.pppoe-user.index', compact('pppoeUsers', 'stats'));
    }
}
