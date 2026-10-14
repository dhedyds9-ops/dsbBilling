<?php

namespace Src\Domain\Notification;

interface NotificationChannelInterface
{
    public function send(Notification $notification): bool;
}
