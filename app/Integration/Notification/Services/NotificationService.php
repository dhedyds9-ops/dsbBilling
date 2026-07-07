<?php

namespace App\Integration\Notification\Services;

use App\Integration\Notification\Contracts\NotificationChannelInterface;
use App\Integration\Notification\Drivers\EmailDriver;
use App\Integration\Notification\Drivers\TelegramDriver;
use App\Integration\Notification\Drivers\WhatsAppDriver;
use App\Models\Alarm;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected array $channels = [];

    public function __construct(
        protected EmailDriver $emailDriver,
        protected WhatsAppDriver $whatsAppDriver,
        protected TelegramDriver $telegramDriver
    ) {
        $this->registerChannel($emailDriver);
        $this->registerChannel($whatsAppDriver);
        $this->registerChannel($telegramDriver);
    }

    public function registerChannel(NotificationChannelInterface $channel)
    {
        $this->channels[$channel->getName()] = $channel;
    }

    public function dispatch(Alarm $alarm, ?array $selectedChannels = null)
    {
        if (empty($selectedChannels)) {
            $selectedChannels = array_keys($this->channels);
        }

        foreach ($selectedChannels as $channelName) {
            if (isset($this->channels[$channelName]) && $this->channels[$channelName]->isEnabled()) {
                try {
                    $this->channels[$channelName]->send($alarm);
                } catch (\Exception $e) {
                    Log::error("Failed to send notification via {$channelName}: " . $e->getMessage(), [
                        'alarm_id' => $alarm->id,
                        'exception' => $e,
                    ]);
                }
            }
        }
    }
}
