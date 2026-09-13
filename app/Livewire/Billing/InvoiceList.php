<?php

namespace App\Livewire\Billing;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Services\Adapters\Payment\PaymentOrchestrationService;

class InvoiceList extends AdminComponent
{
    public array $filters = [
        'search' => '',
        'status' => 'all',
        'billing_cycle' => 'all',
        'date_from' => '',
        'date_to' => '',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public array $selected = [];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
    }

    public function getInvoices()
    {
        return Invoice::with('customer')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getInvoiceStats(): array
    {
        return [
            'total' => Invoice::count(),
            'total_amount' => Invoice::sum('total_amount'),
            'paid' => Invoice::where('status', 'paid')->count(),
            'pending' => Invoice::where('status', 'unpaid')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'collection_rate' => 0, // placeholder
        ];
    }

    public function markAsPaid(int $invoiceId): void
    {
        // Logic to mark invoice as paid
    }

    public function sendReminder(int $invoiceId): void
    {
        // Logic to send reminder
    }

    public function payWithMidtrans(int $invoiceId, PaymentOrchestrationService $orchestration)
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        if ($invoice->status === 'paid') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Tagihan sudah lunas.']);
            return;
        }
        
        $amountIdr = (int) ($invoice->total_amount - $invoice->paid_amount);

        try {
            $result = $orchestration->initiatePayment(
                gatewayKey: 'midtrans',
                customerId: $invoice->customer_id,
                userId: auth()->id() ?? 1,
                amountIdr: $amountIdr,
                invoiceIds: [$invoice->id],
                paymentMethodCode: null,
                customerName: $invoice->customer->name ?? 'Customer',
                customerEmail: $invoice->customer->email ?? 'cust@example.com',
                customerPhone: $invoice->customer->phone ?? '081234567890',
            );

            if ($result['gateway_response']->success) {
                // Redirect user to midtrans snap page
                return redirect()->away($result['gateway_response']->redirectUrl);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal Midtrans: ' . $result['gateway_response']->errorMessage]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.billing.invoice-list', [
            'invoices' => $this->getInvoices(),
            'stats' => $this->getInvoiceStats(),
        ]);
    }
}
