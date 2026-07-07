<?php

namespace Src\Domain\Workforce\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

readonly class WorkStartedEvent implements DomainEvent {
    public function __construct(
        public Uuid $eventId,
        public Uuid $workOrderId,
        public Uuid $technicianId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $workOrderId,
        Uuid $technicianId,
    ): self {
        return new self(
            Uuid::random(),
            $workOrderId,
            $technicianId,
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
