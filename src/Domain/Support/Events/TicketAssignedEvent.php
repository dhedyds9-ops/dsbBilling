<?php

namespace Src\Domain\Support\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

class TicketAssignedEvent extends DomainEvent
{
    public string $eventId;
    public string $ticketId;
    public string $assigneeId;
    public string $assignedBy;

    public function __construct(
        string $eventId,
        string $ticketId,
        string $assigneeId,
        string $assignedBy,
    ) {
        parent::__construct();
        $this->eventId    = $eventId;
        $this->ticketId   = $ticketId;
        $this->assigneeId = $assigneeId;
        $this->assignedBy = $assignedBy;
    }

    public static function create(
        string $ticketId,
        string $assigneeId,
        string $assignedBy,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $ticketId,
            $assigneeId,
            $assignedBy,
        );
    }

    public function getName(): string
    {
        return 'support.ticket.assigned';
    }
}
