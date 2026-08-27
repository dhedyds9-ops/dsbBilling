<?php

namespace Src\Domain\Support\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class TicketResolvedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $ticketId,
        public string $resolvedBy,
        public ?string $note,
        public DateTimeImmutable $occurredAt,
    ) {}

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
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'support.ticket.resolved';
    }
}
