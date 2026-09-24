<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class MaintenanceTaskCompletedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $taskId,
        public readonly Uuid $workOrderId,
        public readonly Uuid $assignmentId,
        public readonly Uuid $customerId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $taskId,
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
    ): self {
        return new self(
            $taskId,
            $workOrderId,
            $assignmentId,
            $customerId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
