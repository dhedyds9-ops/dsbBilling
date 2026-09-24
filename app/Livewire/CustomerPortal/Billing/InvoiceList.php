<?php

namespace App\Livewire\CustomerPortal\Billing;

use App\Services\CustomerPortal\CustomerBillingService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.customer-app')]
class InvoiceList extends Component
{
    use WithPagination;

    public array $activeGateways = [];

    public function mount()
    {
        try {
            $gateways = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class)->getAll();
            $this->activeGateways = [];
            foreach ($gateways as $k => $g) {
                if (($g['enabled'] ?? false) && !in_array($k, ['manual_transfer', 'bca_va', 'ewallet'])) {
                    $this->activeGateways[$k] = $g;
                }
            }
        } catch (\Throwable $e) {
            \Log::error("InvoiceList mount error: " . $e->getMessage());
        }
    }

    public function pay(int $invoiceId, string $gatewayKey, \App\Services\Adapters\Payment\PaymentOrchestrationService $orchestrator)
    {
        $invoice = \App\Models\Billing\Invoice::with('customer')->findOrFail($invoiceId);
        
        if ($invoice->customer->user_id !== auth()->id()) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Tagihan sudah lunas.']);
            return;
        }

        $amountIdr = (int) max(0, $invoice->total_amount - $invoice->paid_amount);

        if ($amountIdr <= 0) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Tagihan tidak memiliki sisa pembayaran.']);
            return;
        }

        try {
            $result = $orchestrator->initiatePayment(
                gatewayKey: $gatewayKey,
                customerId: $invoice->customer_id,
                userId: auth()->id(),
                amountIdr: $amountIdr,
                invoiceIds: [$invoice->id],
                paymentMethodCode: null,
                customerName: $invoice->customer->name ?? 'Customer',
                customerEmail: $invoice->customer->email ?? 'customer@example.com',
                customerPhone: $invoice->customer->phone ?? '00000',
            );

            if ($result['gateway_response']->success) {
                return redirect()->away($result['gateway_response']->redirectUrl);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal: ' . $result['gateway_response']->errorMessage]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error sistem: ' . $e->getMessage()]);
        }
    }

    public function render(CustomerBillingService $billingService)
    {
        $customer = \App\Models\CRM\Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            abort(403, 'Profil pelanggan tidak ditemukan.');
        }

        $invoices = $billingService->getPaginatedInvoices($customer->id);
        return view('livewire.customer-portal.billing.invoice-list', compact('invoices'));
    }
}

