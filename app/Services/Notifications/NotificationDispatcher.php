<?php

namespace App\Services\Notifications;

use App\Models\Alarm;
use App\Services\Notifications\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    protected array $channels = [];

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
