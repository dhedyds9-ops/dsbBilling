<?php

namespace Src\Domain\Workforce\Events;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

readonly class InstallationTaskCompletedEvent implements DomainEvent {
    public function __construct(
        public Uuid $eventId,
        public Uuid $taskId,
        public Uuid $workOrderId,
        public Uuid $assignmentId,
        public Uuid $customerId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $taskId,
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $workOrderId,
            $assignmentId,
            $customerId,
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
