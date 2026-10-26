<?php

declare(strict_types=1);

namespace App\Services\Pengaturan;

use App\Models\Setting;

/**
 * Service penghubung antara konfigurasi UI WhatsApp Gateway (whatsapp.connection di tabel settings)
 * dengan Driver Registry (Fonnte, Baileys, MPWA, Qontak).
 */
class WhatsAppGatewaySettingsService
{
    /**
     * Ambil konfigurasi driver WhatsApp dari database setting ('whatsapp.connection') atau config default.
     *
     * @param string $driver
     * @return array<string, mixed>
     */
    public function get(string $driver): array
    {
        $conn = Setting::getValue('whatsapp.connection', []);
        $cfg = (array)config("whatsapp.drivers.{$driver}", []);

        // Jika driver yang diminta cocok dengan provider yang dipilih di menu Pengaturan WhatsApp
        $activeProvider = $conn['provider'] ?? 'fonnte';
        if ($activeProvider === $driver || ($activeProvider === 'fonnte' && $driver === 'fonnte')) {
            return array_merge($cfg, [
                'api_token' => $conn['api_token'] ?? ($cfg['api_token'] ?? ''),
                'api_base_url' => $conn['base_url'] ?? ($conn['api_base_url'] ?? ($cfg['api_base_url'] ?? 'https://api.fonnte.com')),
                'sender_number' => $conn['sender_number'] ?? ($cfg['sender_number'] ?? ''),
                'device_id' => $conn['device_id'] ?? ($cfg['device_id'] ?? ''),
                'delay_ms' => ((int)($conn['delay_per_message'] ?? 1)) * 1000,
            ]);
        }

        return $cfg;
    }

    /**
     * Ambil seluruh konfigurasi mentah koneksi WhatsApp
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return (array)Setting::getValue('whatsapp.connection', []);
    }
}
