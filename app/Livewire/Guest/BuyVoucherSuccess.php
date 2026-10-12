<?php

namespace App\Livewire\Guest;

use App\Models\VoucherOrder;
use Livewire\Component;

class BuyVoucherSuccess extends Component
{
    public string $uuid;
    public ?VoucherOrder $voucherOrder = null;

    public function mount(string $uuid)
    {
        $this->uuid = $uuid;
        $this->loadOrder();
    }

    public function loadOrder(): void
    {
        $this->voucherOrder = VoucherOrder::with(['serviceProfile', 'voucher'])
            ->where('uuid', $this->uuid)
            ->first();
    }

    public function checkStatus(): void
    {
        $this->loadOrder();
    }

    public function render()
    {
        $title = 'Status Pembelian Voucher Internet';
        if ($this->voucherOrder && $this->voucherOrder->serviceProfile) {
            $title .= ' - ' . $this->voucherOrder->serviceProfile->name;
        }

        return view('livewire.guest.buy-voucher-success')
            ->layout('layouts.guest', ['title' => $title]);
    }
}
