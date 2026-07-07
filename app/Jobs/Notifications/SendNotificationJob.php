<?php

namespace App\Jobs\Notifications;

use App\Models\Notification\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected NotificationModel $notification)
    {
    }

    public function handle(): void
    {
        // TODO: Implement using new NotificationService
        Log::info("SendNotificationJob executed", [
            'notification' => $this->notification->toArray(),
        ]);
        $this->notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
