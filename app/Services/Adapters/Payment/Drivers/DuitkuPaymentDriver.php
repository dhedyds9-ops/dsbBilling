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
 * Duitku Driver (Ewallet + Retail + VA POP).
 *
 * Signature MD5: merchantKey + merchantCode + billAmount
 *   (old MD5 legacy tapi Duitku masih support untuk saat ini)
 * Docs: https://docs.duitku.com/api/id/
 */
final class DuitkuPaymentDriver implements PaymentGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'duitku'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        return (($this->config['environment'] ?? 'sandbox') === 'sandbox')
            ? 'https://api-sandbox.duitku.com'
            : 'https://api.duitku.com';
    }

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        $merchantCode = (string)($this->config['merchant_id'] ?? '');
        $merchantKey = (string)($this->config['server_key'] ?? '');
        if ($merchantCode === '' || $merchantKey === '') {
            return new CreatePaymentResponse(success: false, errorMessage: 'Duitku merchant_code / merchant_key kosong');
        }
        $paymentMethod = $this->mapMethod($req->paymentMethodCode) ?? 'VC'; // default QRIS
        $signature = md5($merchantCode . $req->orderId . $req->amountIdr . $merchantKey);

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
        $callback = $req->callbackUrl ?: ($this->config['callback_url'] ?? '');
        $returnUrl = $req->successRedirectUrl ?: $req->failureRedirectUrl;

        $payload = [
            'merchantcode'   => $merchantCode,
            'paymentAmount'  => $req->amountIdr,
            'paymentMethod'  => $paymentMethod,
            'merchantOrderId' => $req->orderId,
            'productDetails' => 'Pembayaran Tagihan dsBilling ' . $req->orderId,
            'email'          => $req->customerEmail ?: ('customer+' . $req->orderId . '@dsbilling.local'),
            'phoneNumber'    => $req->customerPhone ?: null,
            'additionalParam' => json_encode(['order_id' => $req->orderId]),
            'merchantUserInfo' => $req->customerName ?: 'Customer',
            'customerVaName' => $req->customerName ?: 'Customer dsBilling',
            'callbackUrl'    => $callback,
            'returnUrl'      => $returnUrl,
            'signature'      => $signature,
            'itemDetails'    => $items,
            'expiryPeriod'   => (int)$req->expiryMinutes,
        ];

        try {
            $resp = Http::asJson()
                ->timeout(20)
                ->post($this->baseUrl() . '/web/api/merchant/createInvoice', $payload);
            $raw = $resp->body();
            if (!$resp->successful()) {
                return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: 'Duitku API: ' . $resp->status() . ' - ' . mb_substr($raw, 0, 500));
            }
            $data = json_decode($raw, true) ?: [];
            if (!empty($data['statusCode']) && (string)$data['statusCode'] !== '00') {
                return new CreatePaymentResponse(success: false, rawResponse: $raw, errorMessage: (string)($data['statusMessage'] ?? 'Duitku error'));
            }
            $reference = (string)($data['reference'] ?? '');
            $vaNumber = (string)($data['vaNumber'] ?? '');
            $qrUrl = (string)($data['qrUrl'] ?? '');
            $payUrl = (string)($data['paymentUrl'] ?? '');
            $rCode = (string)($data['rq'] ?? '');

            $mode = 'redirect';
            if ($qrUrl !== '' || str_starts_with($paymentMethod, 'VC')) $mode = 'qr_code';
            elseif ($vaNumber !== '') $mode = 'va_number';
            elseif ($rCode !== '') $mode = 'retail_code';

            return new CreatePaymentResponse(
                success: true,
                gatewayReferenceId: $reference,
                paymentMode: $mode,
                redirectUrl: $payUrl,
                qrContent: $qrUrl,
                bankCode: $this->bankFromMethod($paymentMethod),
                vaNumber: $vaNumber,
                retailOutlet: $this->retailFromMethod($paymentMethod),
                retailCode: $rCode,
                expiryAtEpoch: (int)(time() + ($req->expiryMinutes * 60)),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new CreatePaymentResponse(success: false, errorMessage: $e->getMessage());
        }
    }

    public function checkStatus(string $externalReference): PaymentStatusResponse
    {
        $merchantCode = (string)($this->config['merchant_id'] ?? '');
        $merchantKey = (string)($this->config['server_key'] ?? '');
        try {
            $signature = md5($merchantCode . $externalReference . '700' . $merchantKey);
            $resp = Http::asJson()
                ->timeout(10)
                ->post($this->baseUrl() . '/web/api/merchant/transactionStatus', [
                    'merchantcode'    => $merchantCode,
                    'merchantOrderId' => $externalReference,
                    'signature'       => $signature,
                ]);
            $raw = $resp->body();
            $data = json_decode($raw, true) ?: [];
            $sc = (string)($data['statusCode'] ?? '');
            if ($sc === '00') {
                return new PaymentStatusResponse(status: 'paid', gatewayReferenceId: (string)($data['reference'] ?? $externalReference), paidAmountIdr: (int)($data['amount'] ?? 0), paidAtIso: '', rawResponse: $raw);
            }
            return new PaymentStatusResponse(status: 'pending', gatewayReferenceId: $externalReference, rawResponse: $raw, message: (string)($data['statusMessage'] ?? ''));
        } catch (\Throwable $e) {
            return new PaymentStatusResponse(status: 'failed', message: $e->getMessage());
        }
    }

    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent
    {
        $data = json_decode($rawBody, true) ?: [];
        $merchantCode = (string)($this->config['merchant_id'] ?? '');
        $merchantKey = (string)($this->config['server_key'] ?? '');

        $orderId = (string)($data['merchantOrderId'] ?? '');
        $amount = (int)($data['resultAmount'] ?? 0);
        $recvdSig = (string)($data['signature'] ?? '');
        $expected = md5($merchantCode . $amount . $merchantKey);
        if (!hash_equals($expected, strtolower($recvdSig))) {
            throw InvalidPaymentSignatureException::create(self::driverKey(), 'signature md5 mismatch');
        }

        $sc = (string)($data['statusCode'] ?? '');
        $eventType = match ($sc) {
            '00' => 'payment.paid',
            '01' => 'payment.pending',
            default => 'payment.failed',
        };

        return new WebhookEvent(
            eventType: $eventType,
            gatewayOrderId: (string)($data['reference'] ?? ''),
            merchantOrderId: $orderId,
            amountIdr: $amount,
            paidAmountIdr: $amount,
            paymentMethod: (string)($data['paymentMethod'] ?? ''),
            paidAtIso: now()->toIso8601String(),
            signatureVerifiedBy: 'duitku_md5_merchant_key',
            rawBodyHash: hash('sha256', $rawBody),
            rawPayload: $data,
        );
    }

    private function mapMethod(?string $code): ?string
    {
        return match (strtolower((string)$code)) {
            'qris', 'qr', null => 'VC',
            'gopay' => 'GP',
            'ovo' => 'OV',
            'dana' => 'DA',
            'shopeepay' => 'SP',
            'bca_va', 'bca' => 'BC',
            'mandiri_va' => 'M2',
            'bni_va' => 'BN',
            'bri_va' => 'BT',
            'indomaret' => 'I1',
            'alfamart' => 'A1',
            default => null,
        };
    }

    private function bankFromMethod(string $m): string
    {
        return match ($m) {
            'BC' => 'bca', 'M2' => 'mandiri', 'BN' => 'bni', 'BT' => 'bri',
            default => '',
        };
    }

    private function retailFromMethod(string $m): string
    {
        return match ($m) {
            'I1' => 'indomaret', 'A1' => 'alfamart',
            default => '',
        };
    }
}
