<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\ValueObjects;

/**
 * SSOT: Normalized Webhook Event dari 4 provider manapun.
 *
 * Signature harus SUDAH DIVERIFIKASI oleh method verifyAndParseWebhook() di driver,
 * jadi pemanggil (WebhookController) tidak perlu cek signature lagi.
 *
 * eventType:
 *   payment.paid        = sukses bayar
 *   payment.pending     = VA generated / QR muncul tapi belum bayar
 *   payment.expired     = link / VA expired
 *   payment.failed      = gagal (kartu decline, dll)
 *   payment.refunded    = refund
 *   payment.chargeback  = chargeback
 */
final readonly class WebhookEvent
{
    public function __construct(
        public string  $eventType,            // payment.paid | payment.pending | payment.expired | payment.failed | payment.refunded
        public string  $gatewayOrderId = '',  // external id di sisi gateway
        public string  $merchantOrderId = '',  // order_id internal kita (CreatePaymentRequest.orderId)
        public int     $amountIdr = 0,
        public int     $paidAmountIdr = 0,    // bisa berbeda dari amountIdr kalau underpayment (partial)
        public string  $paymentMethod = '',   // qris | bca_va | cc | gopay | dll
        public string  $paidAtIso = '',
        public string  $signatureVerifiedBy = '',   // 'midtrans_sha512' | 'xendit_hmacsha256' | etc, untuk audit
        public string  $rawBodyHash = '',           // sha256 hex dari raw body, anti-duplicate idempotency
        public array   $rawPayload = [],            // original JSON payload decode assoc
    ) {
        if (!in_array($this->eventType, [
            'payment.paid', 'payment.pending', 'payment.expired',
            'payment.failed', 'payment.refunded', 'payment.chargeback', 'payment.partial',
        ], true)) {
            throw new \InvalidArgumentException("eventType tidak valid: {$this->eventType}");
        }
    }

    public function isSuccess(): bool
    {
        return in_array($this->eventType, ['payment.paid', 'payment.partial'], true);
    }
}
