<?php

namespace App\Integration\Notification\Drivers;

use App\Integration\Notification\Contracts\NotificationChannelInterface;
use App\Models\Alarm;
use Illuminate\Support\Facades\Log;

class EmailDriver implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'email';
    }

    public function send(Alarm $alarm, array $config = []): bool
    {
        // TODO: Implement actual email integration
        Log::info("Email notification sent (simulated) for alarm: {$alarm->id}", [
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
