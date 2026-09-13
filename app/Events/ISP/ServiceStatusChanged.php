<?php

namespace App\Events\ISP;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ServiceStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $customerServiceId;
    public string $oldStatus;
    public string $newStatus;
    public string $serviceType; // 'pppoe', 'hotspot', 'static'
    public Carbon $occurredAt;
    public string $eventId;

    public function __construct(int $customerServiceId, string $oldStatus, string $newStatus, string $serviceType = 'pppoe', ?Carbon $occurredAt = null)
    {
        $this->customerServiceId = $customerServiceId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->serviceType = $serviceType;
        $this->occurredAt = $occurredAt ?? now();
        $this->eventId = \Illuminate\Support\Str::uuid()->toString();
    }
}
