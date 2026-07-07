<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use App\Models\CRM\Customer;
use App\Models\Billing\Invoice;
use App\Services\Billing\BillingAutomationService;

class Create extends AdminComponent
{
    public $customer_id;
    public $amount;
    public $currency = 'IDR';
    public $method = 'bank_transfer';
    public $status = 'pending';
    public $reference_number;
    public $paid_at;
    public $gateway = 'manual';
    public $invoice_ids = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'payments';
        $this->reference_number = 'PAY-' . now()->format('YmdHis');
        $this->paid_at = now()->format('Y-m-d');
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Payments', 'url' => route('billing.payments.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'customer_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:IDR,USD',
            'method' => 'required|in:bank_transfer,cash,credit_card,e_wallet',
            'status' => 'required|in:pending,success,failed',
            'reference_number' => 'required|unique:payments,reference_number',
            'paid_at' => 'nullable|date',
        ]);

        $payment = Payment::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'customer_id' => $this->customer_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'paid_at' => $this->paid_at,
            'gateway' => $this->gateway,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        if (!empty($this->invoice_ids)) {
            $payment->invoices()->attach($this->invoice_ids);
            
            // If payment is successful, verify it for each attached invoice
            if ($this->status === 'success') {
                $billingService = app(BillingAutomationService::class);
                foreach ($this->invoice_ids as $invoiceId) {
                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $billingService->verifyPayment($invoice, $this->amount);
                    }
                }
            }
        }

        session()->flash('success', 'Payment berhasil dibuat!');
        return redirect()->route('billing.payments.show', $payment->id);
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->get();
        $invoices = Invoice::where('customer_id', $this->customer_id)
                          ->where('status', '!=', 'paid')
                          ->get();
        
        return view('livewire.billing.payment.create', compact('customers', 'invoices'));
    }
}
