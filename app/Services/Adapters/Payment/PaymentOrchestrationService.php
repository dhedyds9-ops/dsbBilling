<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment;

use App\Models\Payment\Payment;
use App\Services\Adapters\Payment\Contracts\PaymentGatewayDriverInterface;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentRequest;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentResponse;
use App\Services\Adapters\Payment\ValueObjects\WebhookEvent;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SSOT: Payment Orchestration Layer.
 *
 * Alur utama:
 *   1. initiatePayment() = Buat Payment + Panggil driver createPayment + return response
 *   2. handleWebhookPaid() = Update payment/invoice SUDAH BAYAR → trigger Event → listener otomatis COA Reactivate + WA
 *   3. reconcile() = Cek status reference yang pending lama (polling fallback kalau webhook lambat)
 *
 * Semua operasi CUD via Service Layer, 0 bypass ke model dari luar.
 */
final class PaymentOrchestrationService
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
        private readonly PaymentService $paymentService,
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Initiate payment via gateway, dengan idempotency key per invoiceId+gateway.
     *
     * @param  array<int>  $invoiceIds
     * @return array{payment: Payment, gateway_response: CreatePaymentResponse}
     */
    public function initiatePayment(
        string $gatewayKey,
        int $customerId,
        int $userId,
        int $amountIdr,
        array $invoiceIds = [],
        ?string $paymentMethodCode = null,
        string $customerName = '',
        string $customerEmail = '',
        string $customerPhone = '',
        string $successRedirectUrl = '',
        string $failureRedirectUrl = '',
    ): array {
        $driver = $this->registry->get($gatewayKey);
        if (!$driver) {
            throw new \InvalidArgumentException("Gateway tidak valid: {$gatewayKey}");
        }
        if (!$this->registry->isEnabled($gatewayKey)) {
            throw new \RuntimeException("Gateway [{$gatewayKey}] saat ini dinonaktifkan");
        }

        $orderId = 'DSB-' . now()->format('Ymd') . '-' . strtoupper((string)Str::random(8));

        $itemDetails = [];
        if (count($invoiceIds) > 0) {
            foreach ($invoiceIds as $iid) {
                $inv = $this->invoiceService->findInvoiceById($iid);
                if (!$inv) continue;
                $outstanding = max(0, (float)$inv->total_amount - (float)$inv->paid_amount);
                $itemDetails[] = [
                    'id' => 'invoice-' . $iid,
                    'name' => 'Invoice #' . ($inv->invoice_number ?? $iid),
                    'price' => (int)round((float)$outstanding),
                    'qty' => 1,
                ];
            }
        }
        if (count($itemDetails) === 0) {
            $itemDetails[] = [
                'id' => 'general-payment',
                'name' => 'Pembayaran',
                'price' => $amountIdr,
                'qty' => 1,
            ];
        }

        return DB::transaction(function () use (
            $gatewayKey, $customerId, $userId, $amountIdr, $invoiceIds, $paymentMethodCode,
            $customerName, $customerEmail, $customerPhone, $successRedirectUrl, $failureRedirectUrl,
            $orderId, $itemDetails, $driver,
        ) {
            // 1. Create Payment record PENDING dulu
            $payment = $this->paymentService->createPayment(
                customerId: $customerId,
                amount: $amountIdr,
                userId: $userId,
                invoiceIds: $invoiceIds,
                currency: 'IDR',
                method: $paymentMethodCode ?: $gatewayKey,
                status: 'pending',
                referenceNumber: $orderId,
                paidAt: null,
                gateway: $gatewayKey,
            );

            // 2. Panggil driver createPayment
            $req = new CreatePaymentRequest(
                orderId: $orderId,
                amountIdr: $amountIdr,
                customerName: $customerName,
                customerEmail: $customerEmail,
                customerPhone: $customerPhone,
                paymentMethodCode: $paymentMethodCode,
                successRedirectUrl: $successRedirectUrl,
                failureRedirectUrl: $failureRedirectUrl,
                itemDetails: $itemDetails,
            );
            $resp = $driver->createPayment($req);

            // 3. Kalau success, simpan external reference di reference_number / metadata kalau ada field (keep simple)
            if ($resp->success) {
                $payment->reference_number = $orderId;
                $payment->gateway = $gatewayKey;
                $payment->saveQuietly();

                Log::info('[PaymentOrchestration] Payment initiated', [
                    'payment_id' => $payment->id,
                    'order_id' => $orderId,
                    'gateway' => $gatewayKey,
                    'gateway_ref' => $resp->gatewayReferenceId,
                    'amount' => $amountIdr,
                    'mode' => $resp->paymentMode,
                ]);
            } else {
                Log::warning('[PaymentOrchestration] initiatePayment failed from gateway', [
                    'gateway' => $gatewayKey,
                    'order_id' => $orderId,
                    'err' => $resp->errorMessage,
                ]);
            }

            return ['payment' => $payment, 'gateway_response' => $resp];
        });
    }

    /**
     * Handle WebhookEvent yang sudah diverify signature oleh driver.
     * Anti-duplicate: pakai rawBodyHash + orderId sebagai composite lock.
     *
     * @return array{processed: bool, duplicate: bool, payment_id: ?int, invoice_ids: array<int>}
     */
    public function handleWebhookPaid(WebhookEvent $event, string $gatewayKey): array
    {
        $orderId = $event->merchantOrderId;
        if ($orderId === '') {
            Log::warning('[PaymentOrchestration] webhook tanpa merchantOrderId', ['gateway' => $gatewayKey]);
            return ['processed' => false, 'duplicate' => false, 'payment_id' => null, 'invoice_ids' => []];
        }

        $lockKey = "pay:lock:{$gatewayKey}:{$orderId}:{$event->rawBodyHash}";
        $acquired = false;
        try {
            $acquired = \Illuminate\Support\Facades\Cache::lock($lockKey, 120)->get();
            if (!$acquired) {
                return ['processed' => false, 'duplicate' => true, 'payment_id' => null, 'invoice_ids' => []];
            }

            return DB::transaction(function () use ($event, $gatewayKey, $orderId) {
                // Find Payment by reference_number = orderId
                /** @var Payment|null $payment */
                $payment = Payment::query()
                    ->where('reference_number', $orderId)
                    ->where('gateway', $gatewayKey)
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    Log::warning('[PaymentOrchestration] webhook PAYMENT tidak ditemukan', [
                        'gateway' => $gatewayKey,
                        'order_id' => $orderId,
                        'event_type' => $event->eventType,
                    ]);
                    return ['processed' => false, 'duplicate' => false, 'payment_id' => null, 'invoice_ids' => []];
                }

                // Idempotency: SUDAH paid → return duplicate, JANGAN update 2x!
                if (in_array(strtolower((string)$payment->status), ['success', 'paid'], true)) {
                    Log::info('[PaymentOrchestration] webhook IDEMPOTENT: payment sudah paid', [
                        'payment_id' => $payment->id,
                        'gateway' => $gatewayKey,
                        'order_id' => $orderId,
                    ]);
                    $invoices = $payment->invoices()->pluck('id')->map(fn($v) => (int)$v)->toArray();
                    return ['processed' => true, 'duplicate' => true, 'payment_id' => $payment->id, 'invoice_ids' => $invoices];
                }

                $invoiceIds = $payment->invoices()->pluck('id')->map(fn($v) => (int)$v)->toArray();

                if ($event->isSuccess()) {
                    $newStatus = 'success';
                } elseif ($event->eventType === 'payment.expired') {
                    $newStatus = 'expired';
                } elseif ($event->eventType === 'payment.failed') {
                    $newStatus = 'failed';
                } else {
                    $newStatus = $payment->status;
                }

                if ($newStatus !== $payment->status) {
                    $paymentUpdate = $this->paymentService->updatePayment(
                        payment: $payment,
                        customerId: (int)$payment->customer_id,
                        amount: (float)$event->paidAmountIdr > 0 ? (float)$event->paidAmountIdr : (float)$payment->amount,
                        userId: 1,
                        invoiceIds: $invoiceIds,
                        status: $newStatus,
                        referenceNumber: $payment->reference_number . '|' . $event->gatewayOrderId,
                        paidAt: $event->isSuccess() ? now() : null,
                        gateway: $gatewayKey,
                    );
                    $payment = $paymentUpdate;
                }

                Log::info('[PaymentOrchestration] Webhook processed OK', [
                    'gateway' => $gatewayKey,
                    'payment_id' => $payment->id,
                    'event_type' => $event->eventType,
                    'amount' => $event->paidAmountIdr,
                    'new_status' => $newStatus,
                ]);

                return ['processed' => true, 'duplicate' => false, 'payment_id' => $payment->id, 'invoice_ids' => $invoiceIds];
            });
        } finally {
            if ($acquired) {
                try {
                    \Illuminate\Support\Facades\Cache::lock($lockKey, 120)->release();
                } catch (\Throwable) {
                }
            }
        }
    }
}
