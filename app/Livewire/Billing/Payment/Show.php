<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;

class Show extends AdminComponent
{
    public $paymentId;
    public $payment;

    public function mount($id = null)
    {
        parent::mount();

        $this->paymentId = $id;
        $this->payment = Payment::withoutGlobalScopes()->with(['invoices.customer', 'customer'])->findOrFail($id);
        $this->authorize('view', $this->payment);

        $user = auth()->user();
        $isReseller = $user && ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId()));

        if ($isReseller) {
            $this->activeModule = 'reseller-portal';
            $this->activePage = 'reseller-portal.billing.payments';
            $this->breadcrumbs = [
                ['label' => 'Portal Reseller', 'url' => route('reseller-portal.dashboard')],
                ['label' => 'Data Tagihan', 'url' => route('reseller-portal.billing.payments')],
                ['label' => 'Pembayaran', 'url' => route('reseller-portal.billing.payments')],
                ['label' => $this->payment->reference_number ?? ('#' . $this->payment->id)],
            ];
        } else {
            $this->activeModule = 'billing';
            $this->activePage = 'payments';
            $this->breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Billing', 'url' => route('billing.invoices.index')],
                ['label' => 'Payments', 'url' => route('billing.payments.index')],
                ['label' => $this->payment->reference_number ?? ('#' . $this->payment->id)],
            ];
        }
    }

    public function render()
    {
        return view('livewire.billing.payment.show');
    }
}
