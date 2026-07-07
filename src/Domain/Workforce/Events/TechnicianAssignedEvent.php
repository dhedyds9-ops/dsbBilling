<?php

namespace Src\Domain\Workforce\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

readonly class TechnicianAssignedEvent implements DomainEvent {
    public function __construct(
        public Uuid $eventId,
        public Uuid $assignmentId,
        public Uuid $workOrderId,
        public Uuid $technicianId,
        public ?Uuid $dispatcherId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $assignmentId,
        Uuid $workOrderId,
        Uuid $technicianId,
        ?Uuid $dispatcherId = null,
    ): self {
        return new self(
            Uuid::random(),
            $assignmentId,
            $workOrderId,
            $technicianId,
            $dispatcherId,
            new DateTimeImmutable(),
        );
    }

    public function getEventId(): Uuid {
        return $this->eventId;
    }

    public function getOccurredAt(): DateTimeImmutable {
        return $this->occurredAt;
    }
}
