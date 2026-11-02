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
use Illuminate\Support\Facades\Log;

/**
 * iPaymu Payment Driver (Redirect & Direct Payment - multi channel: VA, QRIS, Retail / CStore).
 *
 * iPaymu API v2 signature HMAC-SHA256:
 *   request:
 *     bodyHash = strtolower(hash('sha256', json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)))
 *     stringToSign = "POST:" . $va . ":" . $bodyHash . ":" . $apiKey
 *     signature = hash_hmac('sha256', $stringToSign, $apiKey)
 *   headers:
 *     va: $va
 *     signature: $signature
 *     timestamp: date('YmdHis')
 *
 *   callback / webhook:
 *     header X-Signature: hash_hmac('sha256', $rawBody, $va)
 * Docs: https://ipaymu.com/id/developer
 */
final class IpaymuPaymentDriver implements PaymentGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string
    {
        return 'ipaymu';
    }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        $env = $this->config['environment'] ?? 'sandbox';
        return ($env === 'sandbox')
            ? 'https://sandbox.ipaymu.com/api/v2'
            : 'https://my.ipaymu.com/api/v2';
    }

    private function getApiKey(): string
    {
        $key = (string)($this->config['api_key'] ?? '');
        if ($key === '') {
            $env = $this->config['environment'] ?? 'sandbox';
            $key = (string)($this->config['api_key_' . $env] ?? ($this->config['api_key_production'] ?? ($this->config['api_key_sandbox'] ?? '')));
        }
        return trim($key);
    }

    private function getVaNumber(): string
    {
        return trim((string)($this->config['va_number'] ?? ''));
    }

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        $apiKey = $this->getApiKey();
        $va = $this->getVaNumber();

        if ($apiKey === '' || $va === '') {
            return new CreatePaymentResponse(
                success: false,
                errorMessage: 'iPaymu VA number atau API key belum diisi di Pengaturan Payment Gateway.'
            );
        }

        $productNames = [];
        $qtys = [];
        $prices = [];
        if (!empty($req->itemDetails) && is_array($req->itemDetails)) {
            foreach ($req->itemDetails as $item) {
                $productNames[] = (string)($item['name'] ?? 'Item');
                $qtys[] = (int)($item['qty'] ?? 1);
                $prices[] = (int)($item['price'] ?? 0);
            }
        }

        if (empty($productNames)) {
            $productNames[] = 'Tagihan #' . $req->orderId;
            $qtys[] = 1;
            $prices[] = (int)$req->amountIdr;
        }

        $notifyUrl = $req->callbackUrl ?: ($this->config['callback_url'] ?? '');
        $returnUrl = $req->successRedirectUrl ?: ($this->config['return_url'] ?? url('/'));
        $cancelUrl = $req->failedRedirectUrl ?: ($this->config['cancel_url'] ?? url('/'));

        $payload = [
            'name'        => $req->customerName ?: 'Customer dsBilling',
            'phone'       => $req->customerPhone ?: '08123456789',
            'email'       => $req->customerEmail ?: ('customer+' . $req->orderId . '@dsbilling.local'),
            'amount'      => (int)$req->amountIdr,
            'notifyUrl'   => $notifyUrl,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
            'referenceId' => (string)$req->orderId,
            'product'     => $productNames,
            'qty'         => $qtys,
            'price'       => $prices,
            'expired'     => (int)ceil(max(1, $req->expiryMinutes) / 60),
        ];

        // Specific method / channel jika dipilih customer
        $mappedMethod = $this->mapMethod($req->paymentMethodCode);
        if ($mappedMethod !== null) {
            $payload['paymentMethod'] = $mappedMethod['method'];
            if (!empty($mappedMethod['channel'])) {
                $payload['paymentChannel'] = $mappedMethod['channel'];
            }
        }

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $bodyHash = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $va . ':' . $bodyHash . ':' . $apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp = date('YmdHis');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'va'           => $va,
                'signature'    => $signature,
                'timestamp'    => $timestamp,
            ])->timeout(25)->post($this->baseUrl() . '/payment', $payload);

            $raw = $response->body();
            if (!$response->successful()) {
                return new CreatePaymentResponse(
                    success: false,
                    rawResponse: $raw,
                    errorMessage: 'iPaymu HTTP ' . $response->status() . ' - ' . mb_substr($raw, 0, 500)
                );
            }

            $data = json_decode($raw, true) ?: [];
            if (($data['Status'] ?? 0) != 200 || empty($data['Data']['Url'])) {
                $msg = (string)($data['Message'] ?? 'iPaymu gagal membuat transaksi pembayaran.');
                return new CreatePaymentResponse(
                    success: false,
                    rawResponse: $raw,
                    errorMessage: $msg
                );
            }

            $resData = $data['Data'] ?? [];
            $redirectUrl = (string)($resData['Url'] ?? '');
            $sessionId = (string)($resData['SessionID'] ?? ($resData['TransactionId'] ?? ''));
            $vaNumber = (string)($resData['PaymentNo'] ?? '');
            $paymentMode = 'redirect';

            if (!empty($resData['QrString'])) {
                $paymentMode = 'qr_code';
            } elseif (!empty($vaNumber) && strtolower((string)($resData['Via'] ?? '')) === 'va') {
                $paymentMode = 'va_number';
            }

            return new CreatePaymentResponse(
                success: true,
                gatewayReferenceId: (string)($resData['TransactionId'] ?? $sessionId),
                paymentMode: $paymentMode,
                redirectUrl: $redirectUrl,
                qrContent: (string)($resData['QrString'] ?? ''),
                bankCode: (string)($resData['Channel'] ?? ''),
                vaNumber: $vaNumber,
                expiryAtEpoch: !empty($resData['Expired']) ? strtotime((string)$resData['Expired']) : (time() + ($req->expiryMinutes * 60)),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            Log::error('[IpaymuPaymentDriver] createPayment error: ' . $e->getMessage());
            return new CreatePaymentResponse(success: false, errorMessage: $e->getMessage());
        }
    }

    public function checkStatus(string $externalReference): PaymentStatusResponse
    {
        $apiKey = $this->getApiKey();
        $va = $this->getVaNumber();

        if ($apiKey === '' || $va === '') {
            return new PaymentStatusResponse(
                status: 'failed',
                message: 'iPaymu VA number / API key belum dikonfigurasi.'
            );
        }

        $payload = [
            'transactionId' => $externalReference,
        ];
        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $bodyHash = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $va . ':' . $bodyHash . ':' . $apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp = date('YmdHis');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'va'           => $va,
                'signature'    => $signature,
                'timestamp'    => $timestamp,
            ])->timeout(15)->post($this->baseUrl() . '/transaction', $payload);

            $raw = $response->body();
            $data = json_decode($raw, true) ?: [];

            if (($data['Status'] ?? 0) != 200) {
                return new PaymentStatusResponse(
                    status: 'failed',
                    rawResponse: $raw,
                    message: (string)($data['Message'] ?? 'iPaymu status check failed')
                );
            }

            $t = $data['Data'] ?? [];
            $statusRaw = strtolower((string)($t['Status'] ?? ($t['status'] ?? '')));
            $statusCode = (string)($t['StatusCode'] ?? ($t['status_code'] ?? ''));

            $mapped = 'pending';
            if (in_array($statusRaw, ['berhasil', 'success', 'paid'], true) || $statusCode === '1') {
                $mapped = 'paid';
            } elseif (in_array($statusRaw, ['expired', 'kadaluarsa'], true) || $statusCode === '-2') {
                $mapped = 'expired';
            } elseif (in_array($statusRaw, ['batal', 'gagal', 'cancel', 'failed'], true)) {
                $mapped = 'failed';
            }

            return new PaymentStatusResponse(
                status: $mapped,
                gatewayReferenceId: (string)($t['TransactionId'] ?? $externalReference),
                paidAmountIdr: (int)($t['Total'] ?? ($t['total'] ?? ($t['Amount'] ?? 0))),
                paidAtIso: (string)($t['Paid'] ?? ($t['paid_at'] ?? '')),
                paymentMethod: (string)($t['Channel'] ?? ($t['channel'] ?? ($t['Via'] ?? ''))),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new PaymentStatusResponse(status: 'failed', message: $e->getMessage());
        }
    }

    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent
    {
        $va = $this->getVaNumber();
        $apiKey = $this->getApiKey();
        $secret = (string)($this->config['webhook_secret'] ?? '');

        $recvd = '';
        foreach ($headers as $k => $v) {
            $kl = strtolower((string)$k);
            if ($kl === 'x-signature' || $kl === 'signature') {
                $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                break;
            }
        }

        // Jika ada secret / VA dan ada header signature yang dikirim
        if ($recvd !== '' && ($va !== '' || $apiKey !== '' || $secret !== '')) {
            $valid = false;
            // iPaymu official callback uses HMAC-SHA256 with VA as secret
            if ($va !== '' && hash_equals(hash_hmac('sha256', $rawBody, $va), $recvd)) {
                $valid = true;
            } elseif ($secret !== '' && hash_equals(hash_hmac('sha256', $rawBody, $secret), $recvd)) {
                $valid = true;
            } elseif ($apiKey !== '' && hash_equals(hash_hmac('sha256', $rawBody, $apiKey), $recvd)) {
                $valid = true;
            }

            if (!$valid) {
                throw InvalidPaymentSignatureException::create(self::driverKey(), 'HMAC SHA256 X-Signature mismatch');
            }
        }

        // Parse body JSON atau Form-Urlencoded
        $data = json_decode($rawBody, true);
        if (!is_array($data) || empty($data)) {
            parse_str($rawBody, $data);
        }

        $trxId = (string)($data['trx_id'] ?? ($data['transaction_id'] ?? ($data['sid'] ?? '')));
        $ref = (string)($data['reference_id'] ?? ($data['referenceId'] ?? ''));
        $amount = (int)($data['total'] ?? ($data['nominal'] ?? ($data['amount'] ?? 0)));

        $statusRaw = strtolower((string)($data['status'] ?? ''));
        $statusCode = (string)($data['status_code'] ?? '');

        $eventType = 'payment.pending';
        if (in_array($statusRaw, ['berhasil', 'success', 'paid'], true) || $statusCode === '1') {
            $eventType = 'payment.paid';
        } elseif (in_array($statusRaw, ['expired', 'kadaluarsa'], true) || $statusCode === '-2') {
            $eventType = 'payment.expired';
        } elseif (in_array($statusRaw, ['refund', 'refunded'], true)) {
            $eventType = 'payment.refunded';
        } elseif (in_array($statusRaw, ['batal', 'gagal', 'cancel'], true)) {
            $eventType = 'payment.failed';
        }

        return new WebhookEvent(
            eventType: $eventType,
            gatewayOrderId: $trxId,
            merchantOrderId: $ref,
            amountIdr: $amount,
            paidAmountIdr: $amount,
            paymentMethod: (string)($data['channel'] ?? ($data['via'] ?? 'ipaymu')),
            paidAtIso: (string)($data['paid_at'] ?? now()->toIso8601String()),
            signatureVerifiedBy: 'ipaymu_hmac_sha256',
            rawBodyHash: hash('sha256', $rawBody),
            rawPayload: is_array($data) ? $data : [],
        );
    }

    private function mapMethod(?string $code): ?array
    {
        return match (strtolower((string)$code)) {
            'qris', 'qr'         => ['method' => 'qris', 'channel' => 'mpm'],
            'va_bca', 'bca'      => ['method' => 'va', 'channel' => 'bca'],
            'va_mandiri', 'mandiri' => ['method' => 'va', 'channel' => 'mandiri'],
            'va_bni', 'bni'      => ['method' => 'va', 'channel' => 'bni'],
            'va_bri', 'bri'      => ['method' => 'va', 'channel' => 'bri'],
            'va_cimb', 'cimb'    => ['method' => 'va', 'channel' => 'cimb'],
            'cstore_alfamart', 'alfamart' => ['method' => 'cstore', 'channel' => 'alfamart'],
            'cstore_indomaret', 'indomaret' => ['method' => 'cstore', 'channel' => 'indomaret'],
            default              => null,
        };
    }
}
