<?php

namespace App\Events\ISP;

use App\Models\ISP\PPPoEUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PPPoEUserStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PPPoEUser $pppoeUser,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}
}
