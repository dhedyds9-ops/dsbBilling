<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment\Contracts;

use App\Services\Adapters\Payment\ValueObjects\CreatePaymentRequest;
use App\Services\Adapters\Payment\ValueObjects\CreatePaymentResponse;
use App\Services\Adapters\Payment\ValueObjects\PaymentStatusResponse;
use App\Services\Adapters\Payment\ValueObjects\WebhookEvent;

/**
 * SSOT: Unified Payment Gateway Driver Interface.
 *
 * Semua driver (Midtrans, Duitku, Tripay, Xendit) wajib implement ini.
 * Prinsip: Strategy Pattern, open/closed. Tambah driver baru = buat class baru, tidak edit file lain.
 */
interface PaymentGatewayDriverInterface
{
    /**
     * Unique driver key: midtrans | duitku | tripay | xendit
     */
    public static function driverKey(): string;

    /**
     * Create payment charge / transaction.
     */
    public function createPayment(CreatePaymentRequest $req): CreatePaymentResponse;

    /**
     * Check status dari order_id / external_id / reference ke gateway API.
     */
    public function checkStatus(string $externalReference): PaymentStatusResponse;

    /**
     * Verify signature webhook (anti-tamper).
     * Return: WebhookEvent yang sudah dinormalisasi, atau throw InvalidSignatureException.
     */
    public function verifyAndParseWebhook(string $rawBody, array $headers): WebhookEvent;

    /**
     * Config dari PaymentGatewaySettingsService (environment, apiKey, merchantId, secret, dll).
     * Dipanggil saat instantiate via Registry.
     */
    public function withConfig(array $config): self;
}
