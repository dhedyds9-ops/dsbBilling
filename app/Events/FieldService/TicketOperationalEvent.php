<?php

declare(strict_types=1);

namespace App\Events\FieldService;

use App\Models\Support\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketOperationalEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $action, // 'created', 'assigned', 'reassigned', 'resolved', 'cancelled'
        public readonly Ticket $ticket,
        public readonly ?int $actorId = null,
        public readonly array $extra = [],
    ) {}
}
