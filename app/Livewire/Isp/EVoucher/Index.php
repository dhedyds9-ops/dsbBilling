<?php

namespace App\Livewire\Isp\EVoucher;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\VoucherOrder;
use Illuminate\Support\Facades\Log;

class Index extends BaseNetworkComponent
{
    public $search = '';
    public $statusFilter = '';

        public function delete($id)
    {
        try {
            $order = \App\Models\VoucherOrder::findOrFail($id);
            $order->delete();
            $this->dispatch('evoucher-deleted');
        } catch (\Exception $e) {
            Log::error('Error deleting evoucher: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = VoucherOrder::with('invoice.payments');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('wa_number', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%')
                  ->orWhere('service_profile_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(20);

        return view('livewire.isp.evoucher.index', compact('orders'));
    }
}