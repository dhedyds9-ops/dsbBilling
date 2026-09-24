<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\ValueObjects;

/**
 * SSOT: Normalized Create Payment Request DTO.
 * Semua driver menerima input ini, sehingga UI / service layer tidak perlu tahu detail masing-masing gateway.
 */
final readonly class CreatePaymentRequest
{
    public function __construct(
        public string  $orderId,                 // internal order id (PAY-xxx atau invoice uuid)
        public int     $amountIdr,               // GROSS AMOUNT dalam Rupiah, integer (no decimal, MANDATORY!)
        public string  $currency = 'IDR',
        public string  $customerName = '',
        public string  $customerEmail = '',
        public string  $customerPhone = '',
        public ?string $paymentMethodCode = null, // cc | bca_va | mandiri_va | qris | gopay | dana | ovo | retail_indomaret
        public string  $successRedirectUrl = '',
        public string  $failureRedirectUrl = '',
        public string  $callbackUrl = '',        // per-request override dari default callback
        public array   $itemDetails = [],        // [['id','name','price','qty'], ...]
        public array   $customerExtras = [],
        public int     $expiryMinutes = 1440,    // default 24 jam
    ) {
        if ($this->amountIdr <= 0) {
            throw new \InvalidArgumentException('amountIdr harus > 0');
        }
        if ($this->orderId === '') {
            throw new \InvalidArgumentException('orderId tidak boleh kosong');
        }
    }
}
