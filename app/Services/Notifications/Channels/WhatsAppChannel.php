<?php

namespace App\Services\Notifications\Channels;

use App\Models\Alarm;
use App\Services\Notifications\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel implements NotificationChannelInterface
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
