<?php

namespace App\Livewire\AAA\Voucher;

use App\Livewire\AAA\BaseAAAComponent;
use App\Models\AAA\Voucher;
use App\Models\Customer;

class Index extends BaseAAAComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'vouchers';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.vouchers.index')],
            ['label' => 'Vouchers'],
        ];
    }

    public function delete($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();
        session()->flash('success', 'Voucher berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Voucher::query()->with(['pool', 'customer']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $vouchers = $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate($this->perPage);

        $stats = [
            'total' => Voucher::count(),
            'available' => Voucher::where('status', 'available')->count(),
            'used' => Voucher::where('status', 'used')->count(),
            'expired' => Voucher::where('status', 'expired')->count(),
        ];

        return view('livewire.aaa.voucher.index', compact('vouchers', 'stats'));
    }
}
