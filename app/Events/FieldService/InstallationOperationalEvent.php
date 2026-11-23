<?php

declare(strict_types=1);

namespace App\Events\FieldService;

use App\Models\CRM\Installation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstallationOperationalEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $action, // 'scheduled', 'assigned', 'completed', 'activated', 'activation_failed'
        public readonly Installation $installation,
        public readonly ?int $actorId = null,
        public readonly array $extra = [],
    ) {}
}
