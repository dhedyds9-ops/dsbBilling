<?php

namespace Src\Domain\Settings\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConnectionUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $type;
    public string|int $connectionId;
    public string $action;
    public string $updatedAt;

    public function __construct(
        int $userId,
        string $type,
        string|int $connectionId,
        string $action,
        string $updatedAt
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->connectionId = $connectionId;
        $this->action = $action;
        $this->updatedAt = $updatedAt;
    }
}
