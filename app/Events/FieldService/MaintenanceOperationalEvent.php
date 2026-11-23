<?php

declare(strict_types=1);

namespace App\Events\FieldService;

use App\Models\Support\SupportMaintenance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceOperationalEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $action, // 'created', 'assigned', 'started', 'completed', 'material_shortage'
        public readonly SupportMaintenance $maintenance,
        public readonly ?int $actorId = null,
        public readonly array $extra = [],
    ) {}
}
