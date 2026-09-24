<?php

namespace App\Events\ISP;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceDiagnosisChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $customerServiceId;
    public string $diagnosis;
    public array $states; // e.g. ['optical' => 'online', 'tr069' => 'stale', 'service' => 'online']

    public function __construct(int $customerServiceId, string $diagnosis, array $states = [])
    {
        $this->customerServiceId = $customerServiceId;
        $this->diagnosis = $diagnosis;
        $this->states = $states;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('customer-status'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DeviceDiagnosisChanged';
    }
}
