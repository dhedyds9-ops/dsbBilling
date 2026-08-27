<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\AdminComponent;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherPool;
use App\Models\ISP\HotspotUser;

class Edit extends AdminComponent
{
    public $voucherId;
    public $voucher;
    public $code;
    public $voucher_pool_id;
    public $hotspot_user_id;
    public $status;
    public $expires_at;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vouchers';
        $this->voucherId = $id;
        $this->voucher = Voucher::findOrFail($id);

        $this->code = $this->voucher->code;
        $this->voucher_pool_id = $this->voucher->voucher_pool_id;
        $this->hotspot_user_id = $this->voucher->hotspot_user_id;
        $this->status = $this->voucher->status;
        $this->expires_at = $this->voucher->expires_at?->format('Y-m-d\TH:i');
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('isp.vouchers.index')],
            ['label' => $this->voucher->code, 'url' => route('isp.vouchers.show', $this->voucherId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:vouchers,code,' . $this->voucherId,
            'status' => 'required|in:available,used,expired',
        ]);

        $this->voucher->update([
            'code' => $this->code,
            'voucher_pool_id' => $this->voucher_pool_id,
            'hotspot_user_id' => $this->hotspot_user_id,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Voucher berhasil diperbarui!');
        return redirect()->route('isp.vouchers.show', $this->voucherId);
    }

    public function render()
    {
        $voucherPools = VoucherPool::all();
        $hotspotUsers = HotspotUser::all();
        return view('livewire.isp.voucher.edit', compact('voucherPools', 'hotspotUsers'));
    }
}
