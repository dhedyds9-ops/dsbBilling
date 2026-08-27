<?php

namespace Src\Domain\Support\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;

readonly class TicketCreatedEvent implements DomainEvent
{
    public function __construct(
        public string $eventId,
        public string $ticketId,
        public string $ticketUuid,
        public string $customerId,
        public string $title,
        public string $priority,
        public ?string $userId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        string $ticketId,
        string $ticketUuid,
        string $customerId,
        string $title,
        string $priority,
        ?string $userId,
    ): self {
        return new self(
            (string) \Illuminate\Support\Str::uuid(),
            $ticketId,
            $ticketUuid,
            $customerId,
            $title,
            $priority,
            $userId,
            new DateTimeImmutable(),
        );
    }

    public function getName(): string
    {
        return 'support.ticket.created';
    }
}
