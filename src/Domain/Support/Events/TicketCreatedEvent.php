<?php

namespace Src\Domain\Support\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TicketCreatedEvent extends DomainEvent
{
    public string $eventId;
    public string $ticketId;
    public string $ticketUuid;
    public string $customerId;
    public string $title;
    public string $priority;
    public ?string $userId;

    public function __construct(
        string $eventId,
        string $ticketId,
        string $ticketUuid,
        string $customerId,
        string $title,
        string $priority,
        ?string $userId,
    ) {
        parent::__construct();
        $this->eventId    = $eventId;
        $this->ticketId   = $ticketId;
        $this->ticketUuid = $ticketUuid;
        $this->customerId = $customerId;
        $this->title      = $title;
        $this->priority   = $priority;
        $this->userId     = $userId;
    }

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
        );
    }

    public function getName(): string
    {
        return 'support.ticket.created';
    }
}
