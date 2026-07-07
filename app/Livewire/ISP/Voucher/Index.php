<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\AAA\Voucher;
use App\Models\ISP\ServiceProfile;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedIds = [];
    public bool $selectAll = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vouchers';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Vouchers'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = Voucher::when($this->showTrashed, fn($q) => $q->withTrashed())
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function delete($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();
        session()->flash('success', 'Voucher berhasil dihapus!');
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
        $query = Voucher::with(['serviceProfile', 'customer'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filters['status'], function($q) {
                $q->where('status', $this->filters['status']);
            });

        $vouchers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => Voucher::count(),
            'available' => Voucher::where('status', 'available')->count(),
            'used' => Voucher::where('status', 'used')->count(),
            'expired' => Voucher::where('status', 'expired')->count(),
        ];

        return view('livewire.isp.voucher.index', compact('vouchers', 'stats'));
    }
}
