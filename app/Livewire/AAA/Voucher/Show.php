<?php

namespace App\Livewire\AAA\Voucher;

use App\Livewire\AdminComponent;
use App\Models\AAA\Voucher;

class Show extends AdminComponent
{
    public $voucherId;
    public $voucher;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'aaa';
        $this->activePage = 'vouchers';
        $this->voucherId = $id;
        $this->voucher = Voucher::with(['voucherPool', 'hotspotUser', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AAA', 'url' => route('aaa.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('aaa.vouchers.index')],
            ['label' => $this->voucher->code],
        ];
    }

    public function render()
    {
        return view('livewire.aaa.voucher.show');
    }
}
