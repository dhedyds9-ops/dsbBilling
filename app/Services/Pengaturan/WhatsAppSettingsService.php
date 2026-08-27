<?php

namespace App\Services\Pengaturan;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Src\Domain\Settings\Events\WhatsAppTestSentEvent;
use Src\Domain\Settings\Events\WhatsAppSettingsUpdatedEvent;

class WhatsAppSettingsService
{
    public const GROUP = 'whatsapp';
    public const PREFIX = 'whatsapp';

    public const PROVIDERS = [
        'fonnte' => 'Fonnte',
        'whacenter' => 'Whacenter',
        'midtrans_wa' => 'Midtrans WhatsApp',
        'custom' => 'Custom',
    ];

    public const NOTIFICATION_KEYS = [
        'customer_new' => 'Pelanggan Baru',
        'invoice_new' => 'Tagihan Baru',
        'overdue_1d' => 'Overdue 1 Hari',
        'overdue_3d' => 'Overdue 3 Hari',
        'overdue_7d' => 'Overdue 7 Hari',
        'payment_success' => 'Payment Sukses',
        'ticket' => 'Ticket Baru',
        'maintenance_info' => 'Info Maintenance',
    ];

    public const TEMPLATE_KEYS = [
        'customer_new' => 'Pelanggan Baru',
        'invoice_new' => 'Tagihan Baru',
        'overdue_1d' => 'Overdue 1 Hari',
        'overdue_3d' => 'Overdue 3 Hari',
        'overdue_7d' => 'Overdue 7 Hari',
        'payment_success' => 'Payment Sukses',
        'ticket' => 'Ticket Baru',
        'maintenance_info' => 'Info Maintenance',
    ];

    protected function defaultTemplates(): array
    {
        return [
            'customer_new' => "Selamat datang {customer_name} di layanan kami!\n\nAkun Anda telah aktif. Nikmati layanan internet terbaik bersama kami.\n\nSupport: {support_phone}",
            'invoice_new' => "Halo {customer_name},\n\nTagihan Anda nomor {invoice_number} telah diterbitkan.\nTotal: Rp {amount}\nJatuh Tempo: {due_date}\n\nSegera lunasi agar layanan tidak terganggu.",
            'overdue_1d' => "Pengingat {customer_name}:\nTagihan {invoice_number} Rp {amount} telah lewat 1 hari jatuh tempo. Segera bayar agar layanan tetap aktif.",
            'overdue_3d' => "Peringatan {customer_name}:\nTagihan {invoice_number} Rp {amount} telah lewat 3 hari. Layanan akan ditangguhkan jika tidak segera dibayar.",
            'overdue_7d' => "PERINGATAN KRITIS {customer_name}:\nTagihan {invoice_number} Rp {amount} telah lewat 7 hari. Layanan akan diisolir hari ini juga.",
            'payment_success' => "Terima kasih {customer_name}!\n\nPembayaran tagihan {invoice_number} Rp {amount} berhasil diterima. Layanan Anda tetap aktif.",
            'ticket' => "Ticket #{ticket_number} telah dibuat untuk {customer_name}.\nSubjek: {ticket_subject}\nKami akan segera menindaklanjuti.",
            'maintenance_info' => "Pemberitahuan Maintenance:\n{maintenance_description}\nWaktu: {maintenance_schedule}\nMohon maaf atas ketidaknyamanan ini.",
        ];
    }

    public function getSettings(): array
    {
        return [
            'provider' => Setting::getValue(self::PREFIX . '.provider', 'fonnte'),
            'api_key' => Setting::getValue(self::PREFIX . '.api_key', ''),
            'api_base_url' => Setting::getValue(self::PREFIX . '.api_base_url', 'https://api.fonnte.com'),
            'sender_number' => Setting::getValue(self::PREFIX . '.sender_number', ''),
            'price_per_sms' => (float) Setting::getValue(self::PREFIX . '.price_per_sms', 0),
            'notifications' => Setting::getValue(self::PREFIX . '.notifications', $this->defaultNotifications()),
        ];
    }

    protected function defaultNotifications(): array
    {
        $result = [];
        foreach (array_keys(self::NOTIFICATION_KEYS) as $k) {
            $result[$k] = true;
        }
        return $result;
    }

    public function getTemplates(): array
    {
        $stored = Setting::getValue(self::PREFIX . '.templates', []);
        $defaults = $this->defaultTemplates();
        $result = [];
        foreach (self::TEMPLATE_KEYS as $key => $label) {
            $result[] = [
                'key' => $key,
                'label' => $label,
                'body' => $stored[$key] ?? $defaults[$key] ?? '',
                'active' => true,
                'updated_at' => now()->format('d/m/Y H:i:s'),
            ];
        }
        return $result;
    }

    public function saveSettings(array $data): array
    {
        $userId = Auth::id() ?? 0;
        $changedKeys = [];
        $existing = $this->getSettings();

        if (isset($data['provider'])) {
            Setting::setValue(self::PREFIX . '.provider', $data['provider'], 'string', self::GROUP);
            if ($existing['provider'] !== $data['provider']) $changedKeys[] = 'provider';
        }
        if (isset($data['api_key'])) {
            Setting::setValue(self::PREFIX . '.api_key', trim($data['api_key']), 'string', self::GROUP);
            if ($existing['api_key'] !== trim($data['api_key'])) $changedKeys[] = 'api_key';
        }
        if (isset($data['api_base_url'])) {
            Setting::setValue(self::PREFIX . '.api_base_url', rtrim(trim($data['api_base_url']), '/'), 'string', self::GROUP);
            if ($existing['api_base_url'] !== rtrim(trim($data['api_base_url']), '/')) $changedKeys[] = 'api_base_url';
        }
        if (isset($data['sender_number'])) {
            Setting::setValue(self::PREFIX . '.sender_number', preg_replace('/[^0-9+]/', '', $data['sender_number']), 'string', self::GROUP);
            if ($existing['sender_number'] !== preg_replace('/[^0-9+]/', '', $data['sender_number'])) $changedKeys[] = 'sender_number';
        }
        if (isset($data['price_per_sms'])) {
            $val = (float) $data['price_per_sms'];
            Setting::setValue(self::PREFIX . '.price_per_sms', $val, 'number', self::GROUP);
            if ($existing['price_per_sms'] !== $val) $changedKeys[] = 'price_per_sms';
        }
        if (isset($data['notifications']) && is_array($data['notifications'])) {
            $normalized = [];
            foreach (array_keys(self::NOTIFICATION_KEYS) as $k) {
                $normalized[$k] = (bool) ($data['notifications'][$k] ?? false);
            }
            Setting::setValue(self::PREFIX . '.notifications', $normalized, 'json', self::GROUP);
            $changedKeys[] = 'notifications';
        }

        Cache::forget(Setting::CACHE_KEY);

        if (count($changedKeys) > 0) {
            Event::dispatch(new WhatsAppSettingsUpdatedEvent(
                userId: $userId,
                changedKeys: $changedKeys,
                updatedAt: now()->toIso8601String(),
            ));
        }

        return [
            'success' => true,
            'changed_keys' => $changedKeys,
            'saved_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public function saveTemplate(string $key, string $body): array
    {
        if (!isset(self::TEMPLATE_KEYS[$key])) {
            return ['success' => false, 'message' => 'Template key tidak valid.'];
        }
        $userId = Auth::id() ?? 0;
        $all = Setting::getValue(self::PREFIX . '.templates', []);
        $all[$key] = $body;
        Setting::setValue(self::PREFIX . '.templates', $all, 'json', self::GROUP);
        Cache::forget(Setting::CACHE_KEY);

        Event::dispatch(new WhatsAppSettingsUpdatedEvent(
            userId: $userId,
            changedKeys: ['template:' . $key],
            updatedAt: now()->toIso8601String(),
        ));

        return ['success' => true, 'template_key' => $key, 'updated_at' => now()->format('d/m/Y H:i:s')];
    }

    public function sendTestMessage(string $phone, string $message = ''): array
    {
        $userId = Auth::id() ?? 0;
        $settings = $this->getSettings();
        if (empty($settings['api_key'])) {
            $res = ['success' => false, 'message' => 'API Key belum dikonfigurasi.'];
            Event::dispatch(new WhatsAppTestSentEvent(
                userId: $userId,
                phone: $phone,
                success: false,
                message: $res['message'],
                sentAt: now()->toIso8601String(),
            ));
            return $res;
        }

        $phoneNormalized = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phoneNormalized, '0')) $phoneNormalized = '62' . substr($phoneNormalized, 1);
        if (!str_starts_with($phoneNormalized, '62')) $phoneNormalized = '62' . $phoneNormalized;

        $msg = $message ?: '*[TEST] dsBilling WhatsApp Gateway*' . "\n\n" . 'Pesan test berhasil dikirim pada ' . now()->format('d/m/Y H:i:s') . "\nProvider: " . self::PROVIDERS[$settings['provider']] ?? $settings['provider'];

        $success = false;
        $responseMessage = '';
        $provider = $settings['provider'];
        $baseUrl = $settings['api_base_url'];
        $apiKey = $settings['api_key'];

        try {
            $payload = match ($provider) {
                'fonnte' => [
                    'target' => $phoneNormalized,
                    'message' => $msg,
                    'url' => '',
                    'delay' => '0',
                ],
                'whacenter' => [
                    'number' => $phoneNormalized,
                    'message' => $msg,
                ],
                default => [
                    'to' => $phoneNormalized,
                    'text' => $msg,
                ],
            };

            $endpoint = match ($provider) {
                'fonnte' => '/send-message',
                'whacenter' => '/api/send-message',
                default => '/send',
            };

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $provider === 'fonnte' ? $apiKey : ('Bearer ' . $apiKey),
                ])
                ->post(rtrim($baseUrl, '/') . $endpoint, $payload);

            $data = $response->json();
            if ($response->successful() && (($data['status'] ?? false) || ($data['success'] ?? false) || ($data['code'] ?? 500) === 200)) {
                $success = true;
                $responseMessage = 'Pesan WA test berhasil dikirim ke ' . $phoneNormalized;
            } else {
                $responseMessage = 'Gagal kirim: ' . ($data['message'] ?? $data['detail'] ?? $response->body());
            }
        } catch (\Throwable $e) {
            $responseMessage = 'Error: ' . $e->getMessage();
        }

        Event::dispatch(new WhatsAppTestSentEvent(
            userId: $userId,
            phone: $phoneNormalized,
            success: $success,
            message: $responseMessage,
            sentAt: now()->toIso8601String(),
        ));

        return [
            'success' => $success,
            'message' => $responseMessage,
            'phone' => $phoneNormalized,
        ];
    }
}
