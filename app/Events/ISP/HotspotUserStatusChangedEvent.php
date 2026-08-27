<?php

namespace App\Events\ISP;

use App\Models\ISP\HotspotUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HotspotUserStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly HotspotUser $hotspotUser,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}
}
