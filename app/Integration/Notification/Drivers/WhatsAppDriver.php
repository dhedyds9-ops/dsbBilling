<?php

namespace App\Integration\Notification\Drivers;

use App\Integration\Notification\Contracts\NotificationChannelInterface;
use App\Models\Alarm;
use Illuminate\Support\Facades\Log;

class WhatsAppDriver implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'whatsapp';
    }

    public function send(Alarm $alarm, array $config = []): bool
    {
        // TODO: Implement actual WhatsApp integration (e.g., using WABA, Twilio, etc.)
        Log::info("WhatsApp notification sent (simulated) for alarm: {$alarm->id}", [
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
