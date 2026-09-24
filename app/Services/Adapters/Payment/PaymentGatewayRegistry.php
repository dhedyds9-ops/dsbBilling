<?php

declare(strict_types=1);

namespace App\Services\Adapters\Payment;

use App\Services\Adapters\Payment\Contracts\PaymentGatewayDriverInterface;
use App\Services\Adapters\Payment\Drivers\DuitkuPaymentDriver;
use App\Services\Adapters\Payment\Drivers\MidtransPaymentDriver;
use App\Services\Adapters\Payment\Drivers\TripayPaymentDriver;
use App\Services\Adapters\Payment\Drivers\XenditPaymentDriver;
use App\Services\Adapters\Payment\Drivers\IpaymuPaymentDriver;
use App\Services\Pengaturan\PaymentGatewaySettingsService;
use App\Services\Adapters\BaseAdapterRegistry;

/**
 * SSOT: Payment Gateway Registry.
 *
 * Lazily instantiate + inject config from PaymentGatewaySettingsService.
 * Pattern: Registry of Strategy drivers.
 */
final class PaymentGatewayRegistry extends BaseAdapterRegistry
{
    private const DRIVERS = [
        'midtrans' => MidtransPaymentDriver::class,
        'duitku' => DuitkuPaymentDriver::class,
        'tripay' => TripayPaymentDriver::class,
        'xendit' => XenditPaymentDriver::class,
        'ipaymu' => IpaymuPaymentDriver::class,
    ];

    public function __construct(
        private readonly PaymentGatewaySettingsService $settings,
    ) {}

    public function get(string $name): ?PaymentGatewayDriverInterface
    {
        if (!isset(self::DRIVERS[$name])) return null;

        if (!isset($this->adapters[$name])) {
            /** @var class-string<PaymentGatewayDriverInterface> $class */
            $class = self::DRIVERS[$name];
            $config = $this->settings->get($name) ?? [];
            $enabled = (bool)($config['enabled'] ?? false);
            if (!$enabled) {
                // Return instantiated tapi disabled (bisa verify signature webhook, tapi createPayment return error)
                // Notifikasi payment bisa saja datang meskipun disabled = karena user bayar di masa lalu
            }
            $instance = new $class();
            $instance->withConfig($config);
            $this->adapters[$name] = $instance;
        }

        /** @var PaymentGatewayDriverInterface */
        return $this->adapters[$name];
    }

    public function isEnabled(string $name): bool
    {
        $cfg = $this->settings->get($name);
        return (bool)($cfg['enabled'] ?? false);
    }

    /**
     * @return array<string,PaymentGatewayDriverInterface>
     */
    public function allEnabledDrivers(): array
    {
        $out = [];
        foreach (array_keys(self::DRIVERS) as $name) {
            if ($this->isEnabled($name)) {
                $d = $this->get($name);
                if ($d) $out[$name] = $d;
            }
        }
        return $out;
    }

    public function allDriverKeys(): array
    {
        return array_keys(self::DRIVERS);
    }
}
