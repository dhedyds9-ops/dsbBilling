<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\ValueObjects;

/**
 * SSOT: Normalized Create Payment Response dari gateway.
 *
 * paymentMode:
 *   - redirect    = user buka redirect_url (Snap Midtrans, Xendit Invoice)
 *   - qr_code     = tampilkan qr string / image (QRIS, GOPAY QR)
 *   - va_number   = tampilkan bank + no VA (BCA, Mandiri, BNI, BRI VA)
 *   - ewallet_deeplink = buka scheme deeplink (dana://, ovo://)
 *   - retail_code = kode pembayaran Indomaret / Alfamart
 */
final readonly class CreatePaymentResponse
{
    public function __construct(
        public bool    $success,
        public string  $gatewayReferenceId = '',  // external id di sisi gateway
        public string  $paymentMode = '',         // redirect | qr_code | va_number | ewallet_deeplink | retail_code
        public string  $redirectUrl = '',         // untuk paymentMode=redirect
        public string  $qrContent = '',           // QRIS string / QR image URL
        public string  $bankCode = '',            // bca | mandiri | bri | bni
        public string  $vaNumber = '',            // untuk VA
        public string  $retailOutlet = '',        // indomaret | alfamart
        public string  $retailCode = '',          // payment code retail
        public string  $ewalletDeeplink = '',
        public int     $expiryAtEpoch = 0,        // UNIX timestamp masa berlaku pembayaran
        public string  $rawResponse = '',         // JSON string mentah dari gateway (untuk debug / audit)
        public string  $errorMessage = '',        // jika success=false
    ) {}
}
