<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Services\Notifications\Telegram\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 30, 60];

    public function __construct(
        public readonly string $chatId,
        public readonly string $text,
        public readonly string $parseMode = 'HTML',
        public readonly ?string $idempotencyKey = null,
    ) {}

    public function middleware(): array
    {
        $lockKey = 'tg-send-' . ($this->idempotencyKey ?? $this->chatId);
        return [
            (new WithoutOverlapping($lockKey))->releaseAfter(5)->expireAfter(300),
        ];
    }

    public function handle(TelegramService $telegram): void
    {
        $res = $telegram->sendMessage($this->chatId, $this->text, $this->parseMode);

        if (!$res['success']) {
            Log::warning("[SendTelegramMessageJob] Pengiriman gagal ke {$this->chatId}: " . ($res['error'] ?? 'Unknown error'));
        }
    }
}
