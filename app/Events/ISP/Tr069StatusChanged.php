<?php

namespace App\Events\ISP;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class Tr069StatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $genieacsDeviceId;
    public string $oldStatus;
    public string $newStatus;
    public Carbon $occurredAt;
    public string $eventId;

    public function __construct(string $genieacsDeviceId, string $oldStatus, string $newStatus, ?Carbon $occurredAt = null)
    {
        $this->genieacsDeviceId = $genieacsDeviceId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->occurredAt = $occurredAt ?? now();
        $this->eventId = \Illuminate\Support\Str::uuid()->toString();
    }
}
