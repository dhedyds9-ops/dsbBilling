<?php

namespace Src\Domain\Support\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TicketResolvedEvent extends DomainEvent
{
    public string $eventId;
    public string $ticketId;
    public string $resolvedBy;
    public ?string $note;

    public function __construct(
        string $eventId,
        string $ticketId,
        string $resolvedBy,
        ?string $note,
    ) {
        parent::__construct();
        $this->eventId    = $eventId;
        $this->ticketId   = $ticketId;
        $this->resolvedBy = $resolvedBy;
        $this->note       = $note;
    }

    public static function create(
        string $ticketId,
        string $resolvedBy,
        ?string $note,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $ticketId,
            $resolvedBy,
            $note,
        );
    }

    public function getName(): string
    {
        return 'support.ticket.resolved';
    }
}
