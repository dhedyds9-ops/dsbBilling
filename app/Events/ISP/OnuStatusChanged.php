<?php

namespace App\Events\ISP;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class OnuStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $onuId;
    public string $oldStatus;
    public string $newStatus;
    public ?float $rxPower;
    public Carbon $occurredAt;
    public string $eventId;

    public function __construct(int $onuId, string $oldStatus, string $newStatus, ?float $rxPower, ?Carbon $occurredAt = null)
    {
        $this->onuId = $onuId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->rxPower = $rxPower;
        $this->occurredAt = $occurredAt ?? now();
        $this->eventId = \Illuminate\Support\Str::uuid()->toString();
    }
}
