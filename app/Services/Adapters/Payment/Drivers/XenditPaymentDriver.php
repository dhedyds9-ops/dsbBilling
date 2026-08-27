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
 * Xendit Driver (VA + QRIS + E-Wallet).
 *
 * Signature webhook: x-callback-token header match config.
 * Docs: https://developers.xendit.co/api-reference/
 */
final class XenditPaymentDriver implements PaymentGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'xendit'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        return 'https://api.xendit.co';
    }

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        $apiKey = (string)($this->config['server_key'] ?? '');
        if ($apiKey === '') return new CreatePaymentResponse(success: false, errorMessage: 'Xendit server_key kosong');

        $method = $req->paymentMethodCode ?? 'invoice';
        try {
            if (str_contains($method, '_va') || $method === 'va') {
                return $this->createVa($apiKey, $req);
            }
            if ($method === 'qris' || $method === 'qr') {
                return $this->createQris($apiKey, $req);
            }
            // Default: Xendit Invoice (support semua payment method via redirect)
            return $this->createInvoice($apiKey, $req);
        } catch (\Throwable $e) {
            return new CreatePaymentResponse(success: false, errorMessage: $e->getMessage());
        }
    }

    private function createInvoice(string $apiKey, CreatePaymentRequest $req): CreatePaymentResponse
    {
        $payload = [
            'external_id' => $req->orderId,
            'amount' => $req->amountIdr,
            'payer_email' => $req->customerEmail ?: ('customer+' . $req->orderId . '@dsbilling.local'),
            'description' => 'Tagihan dsBilling #' . $req->orderId,
            'invoice_duration' => $req->expiryMinutes * 60,
            'success_redirect_url' => $req->successRedirectUrl ?: null,
            'failure_redirect_url' => $req->failureRedirectUrl ?: null,
            'customer' => [
                'given_names' => $req->customerName ?: 'Customer',
                'email' => $req->customerEmail ?: null,
                'mobile_number' => $req->customerPhone ?: null,
            ],
        ];
        if (!empty($this->config['callback_url']) || !empty($req->callbackUrl)) {
            $payload['callback_virtual_account_id'] = null;
        }

        $resp = Http::withBasicAuth($apiKey, '')
            ->timeout(20)
            ->asJson()
            ->post($this->baseUrl() . '/v2/invoices', $payload);
        $raw = $resp->body();
        if (!$resp->successful()) {
            return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Xendit invoice API: ' . $resp->status() . ' - ' . mb_substr($raw, 0, 500));
        }
        $data = json_decode($raw, true) ?: [];
        return new CreatePaymentResponse(
            success: true,
            gatewayReferenceId: (string)($data['id'] ?? ''),
            paymentMode: 'redirect',
            redirectUrl: (string)($data['invoice_url'] ?? ''),
            expiryAtEpoch: (int)($data['expiry_date'] ? strtotime($data['expiry_date']) : (time() + $req->expiryMinutes * 60)),
            rawResponse: $raw,
        );
    }

    private function createVa(string $apiKey, CreatePaymentRequest $req): CreatePaymentResponse
    {
        $bank = match ($req->paymentMethodCode) {
            'bca_va' => 'BCA',
            'bni_va' => 'BNI',
            'bri_va' => 'BRI',
            'mandiri_va' => 'MANDIRI',
            'permata_va' => 'PERMATA',
            default => 'BCA',
        };
        $payload = [
            'external_id' => $req->orderId,
            'bank_code' => $bank,
            'name' => $req->customerName ?: ('Customer ' . $req->orderId),
            'expected_amount' => $req->amountIdr,
            'is_closed' => true,
            'expiration_date' => now()->addMinutes($req->expiryMinutes)->toIso8601String(),
        ];
        $resp = Http::withBasicAuth($apiKey, '')
            ->timeout(20)
            ->asJson()
            ->post($this->baseUrl() . '/callback_virtual_accounts', $payload);
        $raw = $resp->body();
        if (!$resp->successful()) {
            return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Xendit VA: ' . $resp->status() . ' - ' . $raw);
        }
        $data = json_decode($raw, true) ?: [];
        return new CreatePaymentResponse(
            success: true,
            gatewayReferenceId: (string)($data['id'] ?? ''),
            paymentMode: 'va_number',
            bankCode: strtolower($bank),
            vaNumber: (string)($data['account_number'] ?? ''),
            expiryAtEpoch: (int)strtotime((string)($data['expiration_date'] ?? ('+' . $req->expiryMinutes . ' minutes'))),
            rawResponse: $raw,
        );
    }

    private function createQris(string $apiKey, CreatePaymentRequest $req): CreatePaymentResponse
    {
        $payload = [
            'external_id' => $req->orderId,
            'amount' => $req->amountIdr,
            'type' => 'DYNAMIC',
            'expires_at' => now()->addMinutes($req->expiryMinutes)->toIso8601String(),
        ];
        $resp = Http::withBasicAuth($apiKey, '')
            ->timeout(20)
            ->asJson()
            ->post($this->baseUrl() . '/qr_codes', $payload);
        $raw = $resp->body();
        if (!$resp->successful()) {
            return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Xendit QRIS: ' . $resp->status() . ' - ' . $raw);
        }
        $data = json_decode($raw, true) ?: [];
        return new CreatePaymentResponse(
            success: true,
            gatewayReferenceId: (string)($data['id'] ?? ''),
            paymentMode: 'qr_code',
            qrContent: (string)($data['qr_string'] ?? ''),
            expiryAtEpoch: (int)strtotime((string)($data['expires_at'] ?? ('+' . $req->expiryMinutes . ' minutes'))),
            rawResponse: $raw,
        );
    }

    public function checkStatus(string $externalReference): PaymentStatusResponse
    {
        $apiKey = (string)($this->config['server_key'] ?? '');
        try {
            $resp = Http::withBasicAuth($apiKey, '')
                ->timeout(10)
                ->get($this->baseUrl() . '/v2/invoices/' . urlencode($externalReference));
            $raw = $resp->body();
            if (!$resp->successful()) {
                // Coba VA
                $respVa = Http::withBasicAuth($apiKey, '')
                    ->get($this->baseUrl() . '/callback_virtual_account_payments/payment_id=' . urlencode($externalReference));
                if ($respVa->successful()) {
                    $d = json_decode($respVa->body(), true) ?: [];
                    return new PaymentStatusResponse(
                        status: 'paid',
                        gatewayReferenceId: (string)($d['id'] ?? $externalReference),
                        paidAmountIdr: (int)($d['amount'] ?? 0),
                        paidAtIso: (string)($d['transaction_timestamp'] ?? ''),
                        paymentMethod: 'virtual_account',
                        rawResponse: $respVa->body(),
                    );
                }
                return new PaymentStatusResponse(status: 'failed', rawResponse: $raw, message: 'status inquiry failed: ' . $resp->status());
            }
            $data = json_decode($raw, true) ?: [];
            $status = strtolower((string)($data['status'] ?? 'PENDING'));
            $mapped = match ($status) {
                'paid', 'settled' => 'paid',
                'pending' => 'pending',
                'expired' => 'expired',
                default => 'failed',
            };
            return new PaymentStatusResponse(
                status: $mapped,
                gatewayReferenceId: (string)($data['id'] ?? $externalReference),
                paidAmountIdr: (int)($data['amount'] ?? 0),
                paidAtIso: (string)($data['paid_at'] ?? ''),
                paymentMethod: (string)($data['payment_method'] ?? ''),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new PaymentStatusResponse(status: 'failed', message: $e->getMessage());
        }
    }

    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent
    {
        $secret = (string)($this->config['webhook_secret'] ?? '');
        $xToken = '';
        foreach ($headers as $k => $v) {
            $key = strtolower((string)$k);
            if ($key === 'x-callback-token' || $key === 'x-webhook-token') {
                $xToken = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                break;
            }
        }
        if ($secret !== '' && !hash_equals($secret, $xToken)) {
            // Fallback: match dari callback_virtual_accounts.webhook_token di body (Xendit VA)
            $data = json_decode($rawBody, true);
            $tokenFromBody = (string)($data['webhook_token'] ?? '');
            if (!hash_equals($secret, $tokenFromBody)) {
                throw InvalidPaymentSignatureException::create(self::driverKey(), 'x-callback-token mismatch');
            }
        }
        $data = json_decode($rawBody, true) ?: [];

        $id = (string)($data['id'] ?? '');
        $externalId = (string)($data['external_id'] ?? ($data['payment_id'] ?? ($data['order_id'] ?? '')));
        $amount = (int)(($data['amount'] ?? 0) ?: ($data['paid_amount'] ?? 0));
        $statusStr = strtolower((string)($data['status'] ?? ''));
        $eventType = match (true) {
            ($statusStr === 'paid' || isset($data['paid_at']) || ($data['event'] ?? '') === 'invoice.paid') => 'payment.paid',
            ($statusStr === 'expired' || ($data['event'] ?? '') === 'invoice.expired') => 'payment.expired',
            ($statusStr === 'failed') => 'payment.failed',
            default => 'payment.pending',
        };
        // VA payment payload
        if (isset($data['event']) && str_contains((string)$data['event'], 'virtual_account.paid')) {
            $eventType = 'payment.paid';
        }

        return new WebhookEvent(
            eventType: $eventType,
            gatewayOrderId: $id,
            merchantOrderId: $externalId,
            amountIdr: $amount,
            paidAmountIdr: $amount,
            paymentMethod: (string)($data['payment_method'] ?? ($data['bank_code'] ?? '')),
            paidAtIso: (string)($data['paid_at'] ?? ($data['transaction_timestamp'] ?? now()->toIso8601String())),
            signatureVerifiedBy: 'xendit_x_callback_token',
            rawBodyHash: hash('sha256', $rawBody),
            rawPayload: $data,
        );
    }
}
