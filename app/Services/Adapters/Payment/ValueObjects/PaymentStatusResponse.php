<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\ValueObjects;

/**
 * SSOT: Normalized payment status response (check status polling / status inquiry).
 *
 * status:
 *   pending   = menunggu pembayaran
 *   paid      = sukses, SUDAH DITERIMA jumlah penuh (harus update DB!)
 *   expired   = payment link expired, user belum bayar
 *   failed    = transaksi gagal
 *   chargeback= chargeback / refund
 *   partial   = dibayar sebagian (jika gateway support)
 */
final readonly class PaymentStatusResponse
{
    public function __construct(
        public string  $status,               // pending | paid | expired | failed | chargeback | partial
        public string  $gatewayReferenceId = '',
        public int     $paidAmountIdr = 0,
        public string  $paidAtIso = '',       // ISO8601
        public string  $paymentMethod = '',
        public string  $rawResponse = '',
        public string  $message = '',
    ) {}

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->status === 'partial';
    }
}
