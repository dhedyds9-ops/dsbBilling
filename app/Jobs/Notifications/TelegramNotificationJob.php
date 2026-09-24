<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Services\Telegram\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 30, 60];

    public function __construct(
        public readonly string $message,
        public readonly string $chatId,
        public readonly string $parseMode = 'Markdown'
    ) {
        $this->onQueue('notifications-telegram');
    }

    public function handle(TelegramService $telegram): void
    {
        try {
            $success = $telegram->sendDirectMessage($this->message, $this->chatId, $this->parseMode);
            
            if (!$success) {
                // The service already logs the exact API response error. We just trigger a retry by throwing.
                throw new \Exception("Telegram API rejected the message to chat ID {$this->chatId}");
            }
        } catch (\App\Exceptions\RateLimitException $e) {
            Log::warning('[TELEGRAM-JOB] Rate limited by Telegram API.', [
                'chat_id' => $this->chatId,
                'retry_after' => $e->retryAfter
            ]);
            $this->release($e->retryAfter);
        } catch (\Throwable $e) {
            Log::warning('[TELEGRAM-JOB] Failed to send message, will retry.', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
