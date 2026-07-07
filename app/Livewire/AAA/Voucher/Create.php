<?php

namespace App\Livewire\AAA\Voucher;

use App\Livewire\AdminComponent;
use App\Models\AAA\Voucher;
use App\Models\AAA\VoucherPool;
use App\Models\AAA\HotspotUser;

class Create extends AdminComponent
{
    public $code;
    public $voucher_pool_id;
    public $hotspot_user_id;
    public $status = 'available';
    public $expires_at;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'vouchers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('aaa.vouchers.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:vouchers,code',
            'status' => 'required|in:available,used,expired',
        ]);

        Voucher::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'code' => $this->code,
            'voucher_pool_id' => $this->voucher_pool_id,
            'hotspot_user_id' => $this->hotspot_user_id,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Voucher berhasil dibuat!');
        return redirect()->route('aaa.vouchers.index');
    }

    public function render()
    {
        $voucherPools = VoucherPool::all();
        $hotspotUsers = HotspotUser::all();
        return view('livewire.aaa.voucher.create', compact('voucherPools', 'hotspotUsers'));
    }
}
