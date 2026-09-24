<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\AdminComponent;
use App\Models\Billing\Payment;

class Show extends AdminComponent
{
    public $paymentId;
    public $payment;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'payments';
        $this->paymentId = $id;
        $this->payment = Payment::with(['invoice.customer', 'customer'])->findOrFail($id);

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Payments', 'url' => route('billing.payments.index')],
            ['label' => 'Detail Payment'],
        ];
    }

    public function render()
    {
        return view('livewire.billing.payment.show');
    }
}
