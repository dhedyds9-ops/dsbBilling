<?php

namespace Src\Domain\Support\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class TicketAssignedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $ticketId,
        public string $assigneeId,
        public string $assignedBy,
        public DateTimeImmutable $occurredAt,
    ) {}

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
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'support.ticket.assigned';
    }
}
