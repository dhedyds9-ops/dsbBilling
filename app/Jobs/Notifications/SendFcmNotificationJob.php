<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Services\Notifications\Fcm\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $recipientId,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
        public readonly string $channelId = 'channel_general',
        public readonly ?string $idempotencyKey = null,
    ) {
        $this->onQueue('notifications-fcm');
    }

    public function handle(FcmPushService $fcmService): void
    {
        Log::info('[SendFcmNotificationJob] Mengirim notifikasi push FCM', [
            'recipient_id' => $this->recipientId,
            'title'        => $this->title,
            'channel'      => $this->channelId,
        ]);

        $results = $fcmService->sendToUser(
            userId: $this->recipientId,
            title: $this->title,
            body: $this->body,
            data: $this->data,
            channelId: $this->channelId
        );

        Log::info('[SendFcmNotificationJob] Hasil pengiriman FCM', [
            'recipient_id' => $this->recipientId,
            'results'      => $results,
        ]);
    }
}
