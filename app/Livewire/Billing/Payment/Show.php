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
        $this->activeModule = 'billing';
        $this->activePage = 'payments';
        $this->paymentId = $id;
        $this->payment = Payment::with(['customer', 'invoices'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Payments', 'url' => route('billing.payments.index')],
            ['label' => $this->payment->reference_number],
        ];
    }

    public function render()
    {
        $timeline = [
            ['date' => $this->payment->created_at, 'title' => 'Payment Dibuat', 'description' => 'Payment berhasil dibuat', 'type' => 'create'],
        ];
        
        $activities = [
            ['user' => 'Admin', 'action' => 'Membuat payment', 'module' => 'Billing', 'time' => 'Baru saja'],
        ];

        return view('livewire.billing.payment.show', compact('timeline', 'activities'));
    }
}
