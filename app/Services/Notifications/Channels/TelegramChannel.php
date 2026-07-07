<?php

namespace App\Services\Notifications\Channels;

use App\Models\Alarm;
use App\Services\Notifications\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

class TelegramChannel implements NotificationChannelInterface
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
