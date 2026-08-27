<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\AdminComponent;
use App\Models\ISP\Voucher;

class Show extends AdminComponent
{
    public $voucherId;
    public $voucher;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vouchers';
        $this->voucherId = $id;
        $this->voucher = Voucher::with(['voucherPool', 'hotspotUser', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('isp.vouchers.index')],
            ['label' => $this->voucher->code],
        ];
    }

    public function render()
    {
        return view('livewire.isp.voucher.show');
    }
}
