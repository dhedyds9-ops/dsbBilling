<?php

declare(strict_types=1);

namespace App\Services\Notifications\Telegram;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Kirim pesan Telegram ke chat ID tertentu.
     *
     * @return array{success: bool, message_id: ?string, error: ?string}
     */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): array
    {
        $botConfig = Setting::getValue('telegram.bot', []);
        $botToken = trim((string) ($botConfig['bot_token'] ?? config('services.telegram.bot_token', '')));

        if (empty($botToken)) {
            return ['success' => false, 'message_id' => null, 'error' => 'Telegram Bot Token belum dikonfigurasi.'];
        }

        if (empty(trim($chatId))) {
            return ['success' => false, 'message_id' => null, 'error' => 'Chat ID Telegram kosong.'];
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => false,
                ]);

            if ($response->successful() && ($response->json('ok') ?? false)) {
                $msgId = (string) ($response->json('result.message_id') ?? '');
                Log::info("[Telegram] Pesan terkirim ke {$chatId} (Msg ID: {$msgId})");
                return ['success' => true, 'message_id' => $msgId, 'error' => null];
            }

            $errMsg = $response->json('description') ?? substr($response->body(), 0, 250);
            Log::warning("[Telegram] Gagal kirim ke {$chatId}: {$errMsg}");
            return ['success' => false, 'message_id' => null, 'error' => $errMsg];
        } catch (\Throwable $e) {
            Log::warning("[Telegram] Exception saat kirim ke {$chatId}: {$e->getMessage()}");
            return ['success' => false, 'message_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kirim siaran Telegram ke grup/channel terdaftar berdasarkan kategori event.
     */
    public function broadcastToChannels(string $eventType, string $text): int
    {
        $chatIds = Setting::getValue('telegram.chat_ids', []);
        if (empty($chatIds) || !is_array($chatIds)) {
            return 0;
        }

        $sentCount = 0;
        foreach ($chatIds as $item) {
            if (empty($item['enabled']) || empty($item['id'])) {
                continue;
            }

            $events = array_map('trim', explode(',', strtolower((string) ($item['events'] ?? ''))));
            // Jika events kosong atau berisi eventType atau 'all'
            if (empty($item['events']) || in_array(strtolower($eventType), $events, true) || in_array('all', $events, true)) {
                $res = $this->sendMessage((string) $item['id'], $text);
                if ($res['success']) {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }
}
