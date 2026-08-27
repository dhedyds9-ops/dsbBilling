<?php

namespace App\Services\Pengaturan;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Src\Domain\Settings\Events\TelegramTestSentEvent;
use Src\Domain\Settings\Events\TelegramSettingsUpdatedEvent;

class TelegramSettingsService
{
    public const GROUP = 'telegram';
    public const PREFIX = 'telegram';

    public const TEMPLATE_KEYS = [
        'invoice_new' => 'Tagihan Baru',
        'payment_success' => 'Payment Sukses',
        'ticket_new' => 'Ticket Baru',
        'overdue_reminder' => 'Overdue Reminder',
    ];

    public function getSettings(): array
    {
        return [
            'bot_token' => Setting::getValue(self::PREFIX . '.bot_token', ''),
            'webhook_url' => Setting::getValue(self::PREFIX . '.webhook_url', self::generateWebhookUrl()),
            'default_chat_id' => Setting::getValue(self::PREFIX . '.default_chat_id', ''),
            'chat_groups' => Setting::getValue(self::PREFIX . '.chat_groups', []),
        ];
    }

    public function getTemplates(): array
    {
        $templates = Setting::getValue(self::PREFIX . '.templates', []);
        $defaults = $this->defaultTemplates();
        $result = [];
        foreach (self::TEMPLATE_KEYS as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'body' => $templates[$key] ?? $defaults[$key] ?? '',
            ];
        }
        return $result;
    }

    protected function defaultTemplates(): array
    {
        return [
            'invoice_new' => "Halo {customer_name},\n\nTagihan Anda dengan nomor {invoice_number} telah diterbitkan.\nTotal: Rp {amount}\n\nSegera lakukan pembayaran sebelum tanggal jatuh tempo. Terima kasih.",
            'payment_success' => "Halo {customer_name},\n\nPembayaran untuk invoice {invoice_number} sebesar Rp {amount} telah kami terima.\nStatus: LUNAS.\n\nTerima kasih atas kepercayaan Anda.",
            'ticket_new' => "Ticket baru telah dibuat untuk {customer_name}.\nNomor Ticket: {ticket_number}\nSubjek: {ticket_subject}\n\nSegera tindak lanjuti.",
            'overdue_reminder' => "Pengingat untuk {customer_name}:\n\nTagihan {invoice_number} sebesar Rp {amount} telah melewati batas waktu pembayaran.\nSegera lunasi untuk menghindari penangguhan layanan.",
        ];
    }

    protected function generateWebhookUrl(): string
    {
        $token = Setting::getValue(self::PREFIX . '.bot_token', '');
        $base = config('app.url', url('/'));
        return rtrim($base, '/') . '/webhook/telegram/' . md5($token ?: config('app.key'));
    }

    public function saveSettings(array $data): array
    {
        $userId = Auth::id() ?? 0;
        $changedKeys = [];

        $existing = $this->getSettings();

        if (isset($data['bot_token'])) {
            $val = trim($data['bot_token']);
            Setting::setValue(self::PREFIX . '.bot_token', $val, 'string', self::GROUP);
            if ($existing['bot_token'] !== $val) $changedKeys[] = 'bot_token';
        }

        if (isset($data['default_chat_id'])) {
            $val = trim($data['default_chat_id']);
            Setting::setValue(self::PREFIX . '.default_chat_id', $val, 'string', self::GROUP);
            if ($existing['default_chat_id'] !== $val) $changedKeys[] = 'default_chat_id';
        }

        if (isset($data['chat_groups']) && is_array($data['chat_groups'])) {
            $clean = [];
            foreach ($data['chat_groups'] as $g) {
                if (empty($g['chat_id'])) continue;
                $clean[] = [
                    'name' => $g['name'] ?? ('Group ' . $g['chat_id']),
                    'chat_id' => trim($g['chat_id']),
                    'notify_invoice' => (bool) ($g['notify_invoice'] ?? false),
                    'notify_alarm' => (bool) ($g['notify_alarm'] ?? false),
                    'notify_ticket' => (bool) ($g['notify_ticket'] ?? false),
                ];
            }
            Setting::setValue(self::PREFIX . '.chat_groups', $clean, 'json', self::GROUP);
            $changedKeys[] = 'chat_groups';
        }

        $webhook = $this->generateWebhookUrl();
        Setting::setValue(self::PREFIX . '.webhook_url', $webhook, 'string', self::GROUP);

        Cache::forget(Setting::CACHE_KEY);

        if (count($changedKeys) > 0) {
            Event::dispatch(new TelegramSettingsUpdatedEvent(
                userId: $userId,
                changedKeys: $changedKeys,
                updatedAt: now()->toIso8601String(),
            ));
        }

        return [
            'success' => true,
            'changed_keys' => $changedKeys,
            'webhook_url' => $webhook,
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

        Event::dispatch(new TelegramSettingsUpdatedEvent(
            userId: $userId,
            changedKeys: ['template:' . $key],
            updatedAt: now()->toIso8601String(),
        ));

        return ['success' => true, 'template_key' => $key];
    }

    public function sendTestMessage(string $chatId, ?string $botToken = null): array
    {
        $userId = Auth::id() ?? 0;
        $token = $botToken ?: Setting::getValue(self::PREFIX . '.bot_token', '');
        if (empty($token)) {
            $result = [
                'success' => false,
                'message' => 'Bot Token belum dikonfigurasi.',
            ];
            Event::dispatch(new TelegramTestSentEvent(
                userId: $userId,
                chatId: $chatId,
                success: false,
                message: $result['message'],
                sentAt: now()->toIso8601String(),
            ));
            return $result;
        }

        $text = "*[TEST] dsBilling Telegram Bot*\n\nKonfigurasi Telegram Bot berhasil terhubung.\nWaktu: " . now()->format('d/m/Y H:i:s') . "\nChat ID: {$chatId}";
        $success = false;
        $message = '';
        $responseData = null;

        try {
            $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";
            $response = Http::timeout(10)->post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
            $responseData = $response->json();
            if ($response->successful() && ($responseData['ok'] ?? false)) {
                $success = true;
                $message = 'Pesan test berhasil dikirim ke ' . $chatId;
            } else {
                $message = 'API Telegram gagal: ' . ($responseData['description'] ?? $response->body());
            }
        } catch (\Throwable $e) {
            $message = 'Gagal mengirim pesan: ' . $e->getMessage();
        }

        Event::dispatch(new TelegramTestSentEvent(
            userId: $userId,
            chatId: $chatId,
            success: $success,
            message: $message,
            sentAt: now()->toIso8601String(),
        ));

        return [
            'success' => $success,
            'message' => $message,
            'chat_id' => $chatId,
        ];
    }
}
