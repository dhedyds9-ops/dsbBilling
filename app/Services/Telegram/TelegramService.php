<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class TelegramService
{
    protected ?string $botToken = null;
    protected array $chatIds = [];

    public function __construct()
    {
        $botConfig = Setting::getValue('telegram.bot', []);
        $this->botToken = $botConfig['bot_token'] ?? null;
        
        $this->chatIds = Setting::getValue('telegram.chatIds', []);
    }

    public function isEnabled(): bool
    {
        return !empty($this->botToken);
    }

    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'Markdown'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$chatId) {
            Log::warning('Telegram send failed: chatId is required if not specified');
            return false;
        }

        // Dispatch to Queue instead of synchronous blocking call
        \App\Jobs\Notifications\TelegramNotificationJob::dispatch($message, $chatId, $parseMode);
        
        return true;
    }

    /**
     * Executes the actual HTTP POST to Telegram API.
     * Should only be called from TelegramNotificationJob.
     */
    public function sendDirectMessage(string $message, string $chatId, string $parseMode = 'Markdown'): bool
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 60);
                
                // Bounding safety: jangan sampai delay tidak terkendali
                $retryAfter = max(10, min($retryAfter, 3600)); 
                
                throw new \App\Exceptions\RateLimitException("Telegram API Rate Limited", $retryAfter);
            }

            if (!$response->successful()) {
                Log::warning('Telegram send failed', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\App\Exceptions\RateLimitException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Telegram error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendAlarmNotification(string $deviceSN, string $status, string $details): void
    {
        $icon = $status === 'OFFLINE' ? '🔴' : ($status === 'WARNING' ? '⚠️' : 'ℹ️');
        
        $message = "{$icon} *ACS ALARM* {$icon}\n\n";
        $message .= "SN: `{$deviceSN}`\n";
        $message .= "Status: *{$status}*\n";
        $message .= "Detail: {$details}\n";
        $message .= "Waktu: " . now()->format('Y-m-d H:i:s');

        // Loop over configured chats and send to those enabled for 'alarm'
        foreach ($this->chatIds as $chat) {
            if (!empty($chat['enabled']) && str_contains($chat['events'] ?? '', 'alarm')) {
                $this->sendMessage($message, $chat['id']);
            }
        }
    }

    public function sendOnuRedamanAlert(array $data, string $chatId = null): void
    {
        // Menyusun template sesuai request dari user
        $message = "dsbilling Resume: Redaman TINGGI ?\n";
        $message .= "---------------------------\n";
        $message .= "Nomor ID: " . ($data['customer_id'] ?? '-') . "\n";
        $message .= "ONU Status: ONLINE ?\n";
        $message .= "CLID: " . ($data['clid'] ?? '-') . "\n";
        $message .= "Port: " . ($data['port'] ?? '-') . "\n";
        $message .= "ONT S/N: " . ($data['serial_number'] ?? '-') . "\n";
        $message .= "OLT Rx dBm: " . ($data['olt_rx'] ?? '-') . "\n";
        $message .= "ONU Rx dBm: " . ($data['onu_rx'] ?? '-') . " ?\n";
        $message .= "ONU Type: " . ($data['onu_type'] ?? '-') . "\n";
        $message .= "IP Global: " . ($data['ip_address'] ?? '-') . "\n";
        $message .= "Jarak: " . ($data['distance'] ?? '-') . "\n";
        $message .= "----------------------\n";
        $message .= "Paket: " . ($data['package'] ?? '-') . " ??\n";
        $message .= "Profile: " . ($data['profile_up'] ?? '-') . "\n";
        $message .= "Profile: " . ($data['profile_down'] ?? '-') . "\n";
        $message .= "----------------------\n";
        $message .= "Mulai: " . ($data['start_time'] ?? '-') . "\n";
        $message .= "Selesai: " . ($data['end_time'] ?? '') . "\n";
        $message .= "Upload: " . ($data['upload'] ?? '-') . "\n";
        $message .= "Download: " . ($data['download'] ?? '-') . "\n";
        $message .= "Status Koneksi: " . ($data['connection_status'] ?? 'Online') . "\n";

        // Jika chatId dispesifikasikan, kirim langsung
        if ($chatId) {
            $this->sendMessage($message, $chatId, 'HTML');
            return;
        }

        // Broadcast ke semua NOC/Admin yang enable 'alarm' atau 'noc' di pengaturan
        foreach ($this->chatIds as $chat) {
            if (!empty($chat['enabled'])) {
                $this->sendMessage($message, $chat['id'], 'HTML');
            }
        }
    }
}

