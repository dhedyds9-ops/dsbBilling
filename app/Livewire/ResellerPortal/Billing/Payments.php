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
        $resellerId = Auth::user()->getEffectiveResellerId();

        // Query payments where the customer belongs to this reseller
        $query = Payment::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function ($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->where('gateway', '!=', 'reseller_topup')->with(['customer' => fn($q) => $q->withoutGlobalScopes()]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($subQ) {
                      $subQ->withoutGlobalScopes()->where(function($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('code', 'like', '%' . $this->search . '%')
                             ->orWhere('phone', 'like', '%' . $this->search . '%');
                      });
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
