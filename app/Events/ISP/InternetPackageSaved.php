<?php

namespace App\Events\ISP;

use App\Models\ISP\ServiceProfile;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InternetPackageSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceProfile;

    public function __construct(ServiceProfile $serviceProfile)
    {
        $this->serviceProfile = $serviceProfile;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
