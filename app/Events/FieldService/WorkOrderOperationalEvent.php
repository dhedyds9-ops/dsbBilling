<?php

declare(strict_types=1);

namespace App\Events\FieldService;

use App\Models\Workforce\WorkOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderOperationalEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $action, // 'created', 'assigned', 'started', 'completed', 'approved', 'rejected'
        public readonly WorkOrder $workOrder,
        public readonly ?int $actorId = null,
        public readonly array $extra = [],
    ) {}
}
