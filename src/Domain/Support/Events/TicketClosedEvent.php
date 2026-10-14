<?php

namespace Src\Domain\Support\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TicketClosedEvent extends DomainEvent
{
    public string $eventId;
    public string $ticketId;
    public string $closedBy;

    public function __construct(
        string $eventId,
        string $ticketId,
        string $closedBy,
    ) {
        parent::__construct();
        $this->eventId   = $eventId;
        $this->ticketId  = $ticketId;
        $this->closedBy  = $closedBy;
    }

    public static function create(
        string $ticketId,
        string $closedBy,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $ticketId,
            $closedBy,
        );
    }

    public function getName(): string
    {
        return 'support.ticket.closed';
    }
}
