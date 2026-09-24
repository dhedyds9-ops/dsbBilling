<?php

namespace App\Integration\Notification\Drivers;

use App\Integration\Notification\Contracts\NotificationChannelInterface;
use App\Models\Alarm;
use Illuminate\Support\Facades\Log;

class TelegramDriver implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'telegram';
    }

    public function send(Alarm $alarm, array $config = []): bool
    {
        // TODO: Implement actual Telegram integration
        Log::info("Telegram notification sent (simulated) for alarm: {$alarm->id}", [
            'alarm' => $alarm->toArray(),
        ]);
        return true;
    }

    public function isEnabled(): bool
    {
        // TODO: Read from config
        return true;
    }
}
