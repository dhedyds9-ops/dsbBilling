<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use App\Models\Customer;
use App\Models\Billing\Invoice;

class Edit extends AdminComponent
{
    public $paymentId;
    public $payment;
    public $customer_id;
    public $amount;
    public $currency = 'IDR';
    public $method = 'bank_transfer';
    public $status = 'pending';
    public $reference_number;
    public $paid_at;
    public $gateway = 'manual';
    public $invoice_ids = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'payments';
        $this->paymentId = $id;
        $this->payment = Payment::with('invoices')->findOrFail($id);
        
        $this->customer_id = $this->payment->customer_id;
        $this->amount = $this->payment->amount;
        $this->currency = $this->payment->currency;
        $this->method = $this->payment->method;
        $this->status = $this->payment->status;
        $this->reference_number = $this->payment->reference_number;
        $this->paid_at = $this->payment->paid_at?->format('Y-m-d');
        $this->gateway = $this->payment->gateway;
        $this->invoice_ids = $this->payment->invoices->pluck('id')->toArray();
    }

    public function save()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:IDR,USD',
            'method' => 'required|in:bank_transfer,cash,credit_card,e_wallet',
            'status' => 'required|in:pending,success,failed',
            'reference_number' => 'required|unique:payments,reference_number,' . $this->paymentId,
            'paid_at' => 'nullable|date',
        ]);

        $this->payment->update([
            'customer_id' => $this->customer_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'paid_at' => $this->paid_at,
            'gateway' => $this->gateway,
            'updated_by' => auth()->id(),
        ]);

        $this->payment->invoices()->sync($this->invoice_ids);

        session()->flash('success', 'Payment berhasil diperbarui!');
        return redirect()->route('billing.payments.show', $this->paymentId);
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->get();
        $invoices = Invoice::where('customer_id', $this->customer_id)
                          ->where(function($q) {
                              $q->where('status', '!=', 'paid')
                                ->orWhereIn('id', $this->invoice_ids);
                          })
                          ->get();
        
        return view('livewire.billing.payment.edit', compact('customers', 'invoices'));
    }
}
