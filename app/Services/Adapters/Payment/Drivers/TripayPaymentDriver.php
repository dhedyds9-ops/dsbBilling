<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\Drivers;

use App\Services\Adapters\Payment\Contracts\PaymentGatewayDriverInterface;
use App\Services\Adapters\Payment\Exceptions\InvalidPaymentSignatureException;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentRequest;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentResponse;
use App\Services\Adapters\Payment\ValueObjects\PaymentStatusResponse;
use App\Services\Adapters\Payment\ValueObjects\WebhookEvent;
use Illuminate\Support\Facades\Http;

/**
 * Tripay Driver (Closed Payment - multi channel: VA, QRIS, Ewallet, Retail).
 *
 * Tripay signature HMAC-SHA256:
 *   callback: hash_hmac('sha256', $jsonBody, $privateKey)
 *   create: hash_hmac('sha256', merchantCode.$merchantRef.$amount, $privateKey)
 * Docs: https://tripay.co.id/developer
 */
final class TripayPaymentDriver implements PaymentGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'tripay'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        return (($this->config['environment'] ?? 'sandbox') === 'sandbox')
            ? 'https://tripay.co.id/api-sandbox'
            : 'https://tripay.co.id/api';
    }

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        $apiKey = (string)($this->config['server_key'] ?? '');
        $privateKey = (string)($this->config['webhook_secret'] ?? '');
        $merchantCode = (string)($this->config['merchant_id'] ?? '');
        if ($apiKey === '' || $merchantCode === '') {
            return new CreatePaymentResponse(success: false, errorMessage: 'Tripay api_key / merchant_code kosong');
        }

        $method = $this->mapMethod($req->paymentMethodCode);
        if ($method === null) {
            // List channel tersedia jika user tidak pilih method: default QRIS dulu
            $method = 'QRIS';
        }
        $signature = hash_hmac('sha256', $merchantCode . $req->orderId . $req->amountIdr, $privateKey);

        $items = [];
        if (count($req->itemDetails) > 0) {
            foreach ($req->itemDetails as $i) {
                $items[] = [
                    'name' => (string)($i['name'] ?? 'Item'),
                    'price' => (int)($i['price'] ?? 0),
                    'quantity' => (int)($i['qty'] ?? 1),
                ];
            }
        } else {
            $items[] = [
                'name' => 'Pembayaran Tagihan #' . $req->orderId,
                'price' => $req->amountIdr,
                'quantity' => 1,
            ];
        }

        $payload = [
            'method'            => $method,
            'merchant_ref'      => $req->orderId,
            'amount'            => $req->amountIdr,
            'customer_name'     => $req->customerName ?: 'Customer dsBilling',
            'customer_email'    => $req->customerEmail ?: ('customer+' . $req->orderId . '@dsbilling.local'),
            'customer_phone'    => $req->customerPhone ?: null,
            'order_items'       => $items,
            'callback_url'      => $req->callbackUrl ?: ($this->config['callback_url'] ?? ''),
            'return_url'        => $req->successRedirectUrl ?: null,
            'expired_time'      => (int)(time() + ($req->expiryMinutes * 60)),
            'signature'         => $signature,
        ];
        try {
            $resp = Http::withToken($apiKey)
                ->asForm()
                ->timeout(20)
                ->post($this->baseUrl() . '/transaction/create', $payload);
            $raw = $resp->body();
            if (!$resp->successful()) {
                return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Tripay: ' . $resp->status() . ' - ' . mb_substr($raw, 0, 500));
            }
            $data = json_decode($raw, true) ?: [];
            if (($data['success'] ?? false) !== true) {
                return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: (string)($data['message'] ?? 'Tripay gagal'));
            }
            $t = $data['data'] ?? [];
            $payCode = (string)($t['pay_code'] ?? '');
            $payUrl = (string)($t['checkout_url'] ?? '');
            $qr = (string)($t['qr_string'] ?? ($t['qr_url'] ?? ''));
            $mode = 'redirect';
            if ($qr !== '') $mode = 'qr_code';
            elseif ($payCode !== '' && str_starts_with(strtolower($method), 'va')) $mode = 'va_number';
            elseif ($payCode !== '' && str_contains(strtolower($method), 'alfamart')) $mode = 'retail_code';

            return new CreatePaymentResponse(
                success: true,
                gatewayReferenceId: (string)($t['reference'] ?? ''),
                paymentMode: $mode,
                redirectUrl: $payUrl,
                qrContent: $qr,
                bankCode: $this->bankFromMethod($method),
                vaNumber: $payCode,
                retailOutlet: str_contains(strtolower($method), 'alfamart') ? 'alfamart' : (str_contains(strtolower($method), 'indomaret') ? 'indomaret' : ''),
                retailCode: ($mode === 'retail_code') ? $payCode : '',
                expiryAtEpoch: (int)($t['expired_time'] ?? (time() + $req->expiryMinutes * 60)),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new CreatePaymentResponse(success: false, errorMessage: $e->getMessage());
        }
    }

    public function checkStatus(string $externalReference): PaymentStatusResponse
    {
        $apiKey = (string)($this->config['server_key'] ?? '');
        try {
            $resp = Http::withToken($apiKey)
                ->timeout(10)
                ->get($this->baseUrl() . '/transaction/detail', ['reference' => $externalReference]);
            $raw = $resp->body();
            $data = json_decode($raw, true) ?: [];
            if (($data['success'] ?? false) !== true) {
                return new PaymentStatusResponse(status: 'failed', rawResponse: $raw, message: (string)($data['message'] ?? ''));
            }
            $t = $data['data'] ?? [];
            $status = strtolower((string)($t['status'] ?? 'UNPAID'));
            $mapped = match ($status) {
                'paid' => 'paid',
                'unpaid', 'pending' => 'pending',
                'expired' => 'expired',
                default => 'failed',
            };
            return new PaymentStatusResponse(
                status: $mapped,
                gatewayReferenceId: (string)($t['reference'] ?? $externalReference),
                paidAmountIdr: (int)($t['total_amount'] ?? 0),
                paidAtIso: (string)($t['paid_at'] ?? ''),
                paymentMethod: (string)($t['payment_method'] ?? ''),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new PaymentStatusResponse(status: 'failed', message: $e->getMessage());
        }
    }

    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent
    {
        $privateKey = (string)($this->config['webhook_secret'] ?? '');
        $event = '';
        foreach ($headers as $k => $v) {
            if (strtolower((string)$k) === 'x-callback-event') {
                $event = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                break;
            }
        }
        $hmac = hash_hmac('sha256', $rawBody, $privateKey);
        $recvd = '';
        foreach ($headers as $k => $v) {
            if (strtolower((string)$k) === 'x-callback-signature') {
                $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                break;
            }
        }
        if ($privateKey !== '' && !hash_equals($hmac, $recvd)) {
            throw InvalidPaymentSignatureException::create(self::driverKey(), 'hmac sha256 mismatch');
        }

        $data = json_decode($rawBody, true) ?: [];
        $ref = (string)($data['reference'] ?? '');
        $merchantRef = (string)($data['merchant_ref'] ?? '');
        $amount = (int)($data['total_amount'] ?? 0);

        $status = strtolower((string)($data['status'] ?? 'UNPAID'));
        $eventType = match ($status) {
            'paid' => 'payment.paid',
            'expired' => 'payment.expired',
            'refund' => 'payment.refunded',
            default => 'payment.pending',
        };
        if ($event === 'payment.status') {
            // keep mapping from status
        }

        return new WebhookEvent(
            eventType: $eventType,
            gatewayOrderId: $ref,
            merchantOrderId: $merchantRef,
            amountIdr: $amount,
            paidAmountIdr: $amount,
            paymentMethod: (string)($data['payment_method'] ?? ''),
            paidAtIso: (string)($data['paid_at'] ?? now()->toIso8601String()),
            signatureVerifiedBy: 'tripay_hmac_sha256',
            rawBodyHash: hash('sha256', $rawBody),
            rawPayload: $data,
        );
    }

    private function mapMethod(?string $code): ?string
    {
        return match (strtolower((string)$code)) {
            'qris', 'qr' => 'QRIS',
            'gopay' => 'GOPAY',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'shopeepay' => 'SHOPEEPAY',
            'bca_va', 'bca' => 'BCAVA',
            'mandiri_va' => 'MANDIRIVA',
            'bni_va' => 'BNIVA',
            'bri_va' => 'BRIVA',
            'permata_va' => 'PERMATAVA',
            'indomaret' => 'INDOMARET',
            'alfamart' => 'ALFAMART',
            'cc', 'credit_card' => 'CC',
            default => null,
        };
    }

    private function bankFromMethod(string $method): string
    {
        return match (strtolower($method)) {
            'bcava' => 'bca',
            'mandiriva' => 'mandiri',
            'bniva' => 'bni',
            'briva' => 'bri',
            'permatava' => 'permata',
            default => '',
        };
    }
}
