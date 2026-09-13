<?php

namespace App\Livewire\Guest;

use App\Models\VoucherOrder;
use Livewire\Component;

class VoucherOrderSuccess extends Component
{
    public $uuid;
    public $order;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->loadOrder();
        
        if (!$this->order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }
    }
    
    public function loadOrder()
    {
        $this->order = VoucherOrder::where('uuid', $this->uuid)->first();
    }
    
    public function checkStatus()
    {
        $this->loadOrder();
        
        // Return event to stop polling if completed or failed
        if (in_array($this->order->status, [VoucherOrder::STATUS_COMPLETED, VoucherOrder::STATUS_FAILED, VoucherOrder::STATUS_CANCELLED])) {
            $this->dispatch('stop-polling');
        }
    }

    public function render()
    {
        return view('livewire.guest.voucher-order-success')->layout('layouts.guest');
    }
}
