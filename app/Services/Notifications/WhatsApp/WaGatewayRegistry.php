<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp;

use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\Drivers\BaileysRestWaDriver;
use App\Services\Notifications\WhatsApp\Drivers\FonnteWaDriver;
use App\Services\Notifications\WhatsApp\Drivers\MpwaWaDriver;
use App\Services\Notifications\WhatsApp\Drivers\QontakWaDriver;
use App\Services\Pengaturan\WhatsAppGatewaySettingsService;

/**
 * SSOT: WhatsApp Gateway Registry.
 *
 * Strategy Pattern: 4 Driver = Fonnte | MPWA | Baileys | Qontak.
 * Lazily instantiate + inject config via driver.withConfig().
 */
final class WaGatewayRegistry
{
    private const DRIVERS = [
        'fonnte' => FonnteWaDriver::class,
        'mpwa' => MpwaWaDriver::class,
        'baileys' => BaileysRestWaDriver::class,
        'qontak' => QontakWaDriver::class,
    ];

    /** @var array<string, WaGatewayDriverInterface> */
    private array $instances = [];

    public function __construct(
        private readonly ?WhatsAppGatewaySettingsService $settings = null,
    ) {}

    public function get(string $name): ?WaGatewayDriverInterface
    {
        if (!isset(self::DRIVERS[$name])) return null;
        if (isset($this->instances[$name])) return $this->instances[$name];

        /** @var class-string<WaGatewayDriverInterface> $class */
        $class = self::DRIVERS[$name];
        $instance = new $class();
        $cfg = $this->settings?->get($name) ?? [];
        if (count($cfg) > 0) $instance->withConfig($cfg);
        $this->instances[$name] = $instance;
        return $instance;
    }

    /**
     * Primary driver = default aktif di settings, fallback ke fonnte (paling umum ISP Indonesia).
     */
    public function primary(): ?WaGatewayDriverInterface
    {
        // Default primary: dari config whatsp.default_driver, atau yang enabled pertama
        $default = (string)config('whatsapp.default_driver', 'fonnte');
        $primary = $this->get($default);
        if ($primary) return $primary;
        foreach (array_keys(self::DRIVERS) as $k) {
            $d = $this->get($k);
            if ($d) return $d;
        }
        return null;
    }

    public function all(): array
    {
        $out = [];
        foreach (array_keys(self::DRIVERS) as $k) {
            $out[$k] = $this->get($k);
        }
        return array_filter($out);
    }

    public function allDriverKeys(): array { return array_keys(self::DRIVERS); }
}
