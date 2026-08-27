<?php

namespace Src\Domain\Support\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class TicketClosedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $ticketId,
        public string $closedBy,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $ticketId,
        string $closedBy,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $ticketId,
            $closedBy,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'support.ticket.closed';
    }
}
