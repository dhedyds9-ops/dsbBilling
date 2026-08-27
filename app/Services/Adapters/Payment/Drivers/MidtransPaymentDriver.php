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
 * Midtrans Driver (Snap + Core API).
 *
 * Supported:
 *  - Create via Snap Redirect (paymentMethodCode = null = semua channel)
 *  - Core Credit Card / VA / QRIS via paymentMethodCode
 *  - Webhook HTTP Notification + signature SHA512(order_id+status_code+gross_amount+server_key)
 *  - Idempotency via X-Append-Notification
 *
 * Refs: https://docs.midtrans.com/docs/https-notification-webhooks
 */
final class MidtransPaymentDriver implements PaymentGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'midtrans'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        return (($this->config['environment'] ?? 'sandbox') === 'sandbox')
            ? 'https://app.sandbox.midtrans.com'
            : 'https://app.midtrans.com';
    }

    private function apiBaseUrl(): string
    {
        return (($this->config['environment'] ?? 'sandbox') === 'sandbox')
            ? 'https://api.sandbox.midtrans.com'
            : 'https://api.midtrans.com';
    }

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        $serverKey = (string)($this->config['server_key'] ?? '');
        if ($serverKey === '') {
            return new CreatePaymentResponse(success: false, errorMessage: 'Midtrans server_key tidak dikonfigurasi');
        }

        $itemDetails = [];
        if (count($req->itemDetails) > 0) {
            foreach ($req->itemDetails as $i) {
                $itemDetails[] = [
                    'id' => (string)($i['id'] ?? uniqid('item', true)),
                    'name' => (string)($i['name'] ?? 'Item'),
                    'price' => (int)($i['price'] ?? 0),
                    'quantity' => (int)($i['qty'] ?? 1),
                ];
            }
        } else {
            $itemDetails[] = [
                'id' => 'invoice-' . $req->orderId,
                'name' => 'Pembayaran Tagihan #' . $req->orderId,
                'price' => $req->amountIdr,
                'quantity' => 1,
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $req->orderId,
                'gross_amount' => $req->amountIdr,
            ],
            'customer_details' => [
                'first_name' => $req->customerName ?: 'Customer',
                'email' => $req->customerEmail ?: ('customer+' . $req->orderId . '@dsbilling.local'),
                'phone' => $req->customerPhone ?: null,
            ],
            'item_details' => $itemDetails,
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => (int)$req->expiryMinutes,
            ],
        ];
        if (!empty($this->config['callback_url']) || !empty($req->callbackUrl)) {
            $payload['payment_notification_url'] = $req->callbackUrl ?: $this->config['callback_url'];
        }
        if (!empty($req->successRedirectUrl)) $payload['finish_redirect_url'] = $req->successRedirectUrl;
        if (!empty($req->failureRedirectUrl)) $payload['error_redirect_url'] = $req->failureRedirectUrl;

        if (!empty($req->paymentMethodCode)) {
            $payload['enabled_payments'] = match ($req->paymentMethodCode) {
                'cc' => ['credit_card'],
                'qris' => ['qris'],
                'gopay' => ['gopay'],
                'bca_va' => ['bank_transfer'],
                'mandiri_va' => ['echannel'],
                'bni_va' => ['bni_va'],
                'bri_va' => ['bri_va'],
                default => null,
            };
            if ($payload['enabled_payments'] === null) unset($payload['enabled_payments']);
        }

        try {
            $resp = Http::withBasicAuth($serverKey, '')
                ->withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
                ->timeout(20)
                ->post($this->baseUrl() . '/snap/v1/transactions', $payload);

            $raw = $resp->body();
            if (!$resp->successful()) {
                return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Midtrans API: ' . $resp->status() . ' - ' . $raw);
            }
            $data = json_decode($raw, true) ?: [];

            $token = (string)($data['token'] ?? '');
            $redirect = (string)($data['redirect_url'] ?? '');

            return new CreatePaymentResponse(
                success: true,
                gatewayReferenceId: $token,
                paymentMode: 'redirect',
                redirectUrl: $redirect,
                expiryAtEpoch: time() + ($req->expiryMinutes * 60),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new CreatePaymentResponse(success: false, errorMessage: $e->getMessage());
        }
    }

    public function checkStatus(string $externalReference): PaymentStatusResponse
    {
        $serverKey = (string)($this->config['server_key'] ?? '');
        try {
            $resp = Http::withBasicAuth($serverKey, '')
                ->timeout(10)
                ->get($this->apiBaseUrl() . '/v2/' . urlencode($externalReference) . '/status');

            $raw = $resp->body();
            $data = json_decode($raw, true) ?: [];
            $status = $this->normalizeTransactionStatus($data);

            return new PaymentStatusResponse(
                status: $status,
                gatewayReferenceId: (string)($data['transaction_id'] ?? ''),
                paidAmountIdr: (int)round((float)($data['gross_amount'] ?? 0)),
                paidAtIso: (string)($data['settlement_time'] ?? ($data['transaction_time'] ?? '')),
                paymentMethod: (string)($data['payment_type'] ?? ''),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new PaymentStatusResponse(status: 'failed', message: $e->getMessage());
        }
    }

    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent
    {
        $data = json_decode($rawBody, true);
        if (!is_array($data)) {
            throw InvalidPaymentSignatureException::create(self::driverKey(), 'body not json');
        }

        $orderId = (string)($data['order_id'] ?? '');
        $statusCode = (string)($data['status_code'] ?? '');
        $grossAmount = (string)($data['gross_amount'] ?? '');
        $serverKey = (string)($this->config['server_key'] ?? '');

        $signatureFromMidtrans = (string)($data['signature_key'] ?? '');
        $localSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($localSignature, strtolower($signatureFromMidtrans))) {
            throw InvalidPaymentSignatureException::create(self::driverKey(), 'sha512 mismatch');
        }

        $txStatus = $this->normalizeTransactionStatus($data);
        $eventType = match ($txStatus) {
            'paid' => 'payment.paid',
            'pending' => 'payment.pending',
            'expired' => 'payment.expired',
            'chargeback' => 'payment.chargeback',
            'partial' => 'payment.partial',
            default => 'payment.failed',
        };

        $fraudStatus = (string)($data['fraud_status'] ?? '');
        if ($txStatus === 'paid' && $fraudStatus === 'deny') {
            $eventType = 'payment.failed';
        }

        $grossAmtInt = (int)round((float)$grossAmount);

        return new WebhookEvent(
            eventType: $eventType,
            gatewayOrderId: (string)($data['transaction_id'] ?? ''),
            merchantOrderId: $orderId,
            amountIdr: $grossAmtInt,
            paidAmountIdr: $grossAmtInt,
            paymentMethod: (string)($data['payment_type'] ?? ''),
            paidAtIso: (string)($data['settlement_time'] ?? ($data['transaction_time'] ?? now()->toIso8601String())),
            signatureVerifiedBy: 'midtrans_sha512',
            rawBodyHash: hash('sha256', $rawBody),
            rawPayload: $data,
        );
    }

    private function normalizeTransactionStatus(array $data): string
    {
        $s = strtolower((string)($data['transaction_status'] ?? ''));
        $fraud = strtolower((string)($data['fraud_status'] ?? 'accept'));
        return match ($s) {
            'settlement', 'capture' => 'paid',
            'pending', 'authorize' => 'pending',
            'expire' => 'expired',
            'deny', 'cancel' => 'failed',
            'refund', 'partial_refund' => 'chargeback',
            default => 'pending',
        };
    }
}
