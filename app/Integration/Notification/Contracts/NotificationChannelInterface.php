<?php

namespace App\Integration\Notification\Contracts;

use App\Models\Alarm;

interface NotificationChannelInterface
{
    public function getName(): string;

    public function send(Alarm $alarm, array $config = []): bool;

    public function isEnabled(): bool;
}
