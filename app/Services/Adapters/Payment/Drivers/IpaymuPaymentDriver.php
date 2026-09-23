<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\Drivers;

use App\Services\Adapters\Payment\Contracts\PaymentGatewayDriverInterface;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentRequest;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentResponse;
use App\Services\Adapters\Payment\ValueObjects\WebhookEvent;
use App\Services\Adapters\Payment\Exceptions\InvalidPaymentSignatureException;
use Illuminate\Http\Request;

class IpaymuPaymentDriver implements PaymentGatewayDriverInterface
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

    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse
    {
        return new CreatePaymentResponse(
            success: false,
            errorMessage: 'iPaymu gateway creation is not yet implemented.'
        );
    }

    public function verifyWebhook(Request $request): WebhookEvent
    {
        throw InvalidPaymentSignatureException::create(self::driverKey(), 'Not implemented');
    }

    public function getTransactionStatus(string $externalReference): string
    {
        return 'unpaid';
    }
}
