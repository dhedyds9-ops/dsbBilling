<?php

namespace App\Livewire\ResellerPortal\Billing;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;

class Payments extends AdminComponent
{
    public $search = '';
    public $perPage = 10;
    public $status = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.billing.payments';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Tagihan', 'url' => '#'],
            ['label' => 'Riwayat Pembayaran', 'url' => route('reseller-portal.billing.payments')],
        ];
    }

    public function render()
    {
        $resellerId = Auth::id();

        // Query payments where the customer belongs to this reseller
        $query = Payment::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })->where('gateway', '!=', 'reseller_topup')->with(['customer']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($subQ) {
                      $subQ->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('username', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.reseller-portal.billing.payments', [
            'payments' => $payments
        ]);
    }
}
