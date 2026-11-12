<?php

namespace App\Services\Billing;

use App\Models\Billing\Invoice;
use App\Models\Payment\Payment;
use App\Repositories\Billing\InvoiceRepository;
use App\Repositories\Billing\PaymentRepository;
use App\Services\ISP\ISPProvisioningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Domain\Billing\Events\InvoicePaidEvent;
use Src\Domain\Billing\Events\PaymentReceivedEvent;
use Src\Domain\Billing\Events\PaymentVerifiedEvent;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceService $invoiceService,
        protected ?ISPProvisioningService $provisioningService = null,
    ) {}

    public function createPayment(
        int $customerId,
        float $amount,
        int $userId,
        array $invoiceIds = [],
        string $currency = 'IDR',
        string $method = 'bank_transfer',
        string $status = 'pending',
        ?string $referenceNumber = null,
        ?string $gatewayTransactionId = null,
        ?\DateTimeInterface $paidAt = null,
        string $gateway = 'manual',
    ): Payment {
        return DB::transaction(function () use (
            $customerId, $amount, $userId, $invoiceIds, $currency, $method,
            $status, $referenceNumber, $gatewayTransactionId, $paidAt, $gateway
        ) {
            $resolvedReference = $referenceNumber ?? 'PAY-' . now()->format('YmdHis');

            $payment = $this->paymentRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'amount' => $amount,
                'currency' => $currency,
                'method' => $method,
                'status' => $status,
                'reference_number' => $resolvedReference,
                'gateway_transaction_id' => $gatewayTransactionId,
                'paid_at' => $paidAt ?? ($status === 'success' ? now() : null),
                'gateway' => $gateway,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if (!empty($invoiceIds)) {
                $payment->invoices()->attach($invoiceIds);
            }

            Event::dispatch(new PaymentReceivedEvent(
                $payment->uuid,
                !empty($invoiceIds) ? (string) $invoiceIds[0] : '',
                $payment->customer_id,
                $payment->amount,
            ));

            if ($status === 'success' && !empty($invoiceIds)) {
                $this->applyPaymentToInvoices($payment, $invoiceIds, $userId);
            }

            return $payment;
        });
    }

    public function updatePayment(
        Payment $payment,
        int $customerId,
        float $amount,
        int $userId,
        array $invoiceIds = [],
        ?string $currency = null,
        ?string $method = null,
        ?string $status = null,
        ?string $referenceNumber = null,
        ?string $gatewayTransactionId = null,
        ?\DateTimeInterface $paidAt = null,
        ?string $gateway = null,
    ): Payment {
        return DB::transaction(function () use (
            $payment, $customerId, $amount, $userId, $invoiceIds, $currency, $method,
            $status, $referenceNumber, $gatewayTransactionId, $paidAt, $gateway
        ) {
            $previousStatus = $payment->status;

            $payment->update([
                'customer_id' => $customerId,
                'amount' => $amount,
                'currency' => $currency ?? $payment->currency,
                'method' => $method ?? $payment->method,
                'status' => $status ?? $payment->status,
                'reference_number' => $referenceNumber ?? $payment->reference_number,
                'gateway_transaction_id' => $gatewayTransactionId ?? $payment->gateway_transaction_id,
                'paid_at' => $paidAt ?? $payment->paid_at,
                'gateway' => $gateway ?? $payment->gateway,
                'updated_by' => $userId,
            ]);

            $payment->invoices()->sync($invoiceIds);

            $newStatus = $status ?? $payment->status;
            if ($previousStatus !== 'success' && $newStatus === 'success' && !empty($invoiceIds)) {
                $this->applyPaymentToInvoices($payment, $invoiceIds, $userId);
            }

            return $payment;
        });
    }

    protected function applyPaymentToInvoices(Payment $payment, array $invoiceIds, int $userId): void
    {
        $remainingAmount = $payment->amount;

        $invoices = Invoice::withoutGlobalScopes()
            ->whereIn('id', $invoiceIds)
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($invoices as $invoice) {
            if ($remainingAmount <= 0) {
                break;
            }

            $outstanding = $invoice->total_amount - $invoice->paid_amount;
            if ($outstanding <= 0) {
                continue;
            }

            $applyAmount = min($remainingAmount, $outstanding);

            $newPaidAmount = $invoice->paid_amount + $applyAmount;
            $newStatus = $newPaidAmount >= $invoice->total_amount ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new InvoicePaidEvent(
                $invoice->uuid,
                $invoice->customer_id,
                $applyAmount,
            ));

            $remainingAmount -= $applyAmount;

            if ($newStatus === 'paid') {
                $this->handleInvoicePaid($invoice, $userId);
            }
        }

        Event::dispatch(new PaymentVerifiedEvent(
            $payment->uuid,
            $payment->customer_id,
            $payment->amount,
        ));
    }

    public function handleInvoicePaid(Invoice $invoice, int $userId = 1): void
    {
        $customerId = (int) $invoice->customer_id;
        if ($customerId <= 0) {
            return;
        }

        // Cek apakah masih ada invoice lain milik customer ini yang overdue & belum dibayar
        $hasRemainingOverdue = Invoice::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->where('id', '!=', $invoice->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->where('due_date', '<', now()->toDateString())
            ->exists();

        if ($hasRemainingOverdue) {
            Log::info("PaymentService: Pelanggan #{$customerId} masih memiliki tagihan jatuh tempo lain yang belum lunas. Melewati buka isolir otomatis.", [
                'paid_invoice_id' => $invoice->id,
            ]);
            return;
        }

        // 1. Pulihkan status Customer menjadi active
        \App\Models\CRM\Customer::withoutGlobalScopes()
            ->where('id', $customerId)
            ->update(['status' => 'active']);

        // 2. Pulihkan status Subscription (jika ada)
        \App\Models\Billing\Subscription::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'active')
            ->update(['status' => 'active']);

        // 3. Ambil semua layanan (CustomerService) milik pelanggan
        $customerServices = \App\Models\Customer\CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->with(['pppoeUser', 'hotspotUser', 'serviceProfile'])
            ->get();

        foreach ($customerServices as $cs) {
            try {
                $cs->timestamps = false;
                $cs->update([
                    'status' => 'active',
                    'suspended_at' => null,
                    'reactivation_status' => 'success',
                    'activated_at' => $cs->activated_at ?? now(),
                ]);

                // Pulihkan PPPoE User
                if ($cs->pppoeUser) {
                    $pu = $cs->pppoeUser;
                    $pu->timestamps = false;
                    $pu->update([
                        'status' => 'active',
                        'suspended_at' => null,
                    ]);

                    // Sinkronisasi pemulihan ke Router MikroTik
                    try {
                        app(\App\Services\ISP\PPPoEService::class)->provisionToMikrotik($pu);
                    } catch (\Throwable $mikrotikErr) {
                        Log::warning("PaymentService auto-unsuspend MikroTik PPPoE warning for {$pu->username}: " . $mikrotikErr->getMessage());
                    }
                }

                // Pulihkan Hotspot User
                if ($cs->hotspotUser) {
                    $hu = $cs->hotspotUser;
                    $hu->timestamps = false;
                    $hu->update([
                        'status' => 'active',
                        'suspended_at' => null,
                    ]);

                    // Sinkronisasi pemulihan ke Router MikroTik Hotspot
                    try {
                        app(\App\Services\ISP\HotspotService::class)->activateHotspotUser($hu->id, $userId);
                    } catch (\Throwable $hsErr) {
                        Log::warning("PaymentService auto-unsuspend MikroTik Hotspot warning for {$hu->username}: " . $hsErr->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::error("PaymentService auto-unsuspend CustomerService #{$cs->id} error: " . $e->getMessage());
            }
        }

        // 4. Jalankan background job untuk COA / FreeRADIUS PoD (jika antrian aktif)
        try {
            \App\Jobs\Billing\ReactivateCustomerJob::dispatch($invoice);
        } catch (\Throwable $qErr) {
            Log::info("PaymentService dispatch ReactivateCustomerJob info: " . $qErr->getMessage());
        }
    }
}
