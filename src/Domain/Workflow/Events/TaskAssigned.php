<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TaskAssigned implements DomainEvent
{
    public function __construct(
        public readonly Uuid $taskId,
        public readonly Uuid $instanceId,
        public readonly Uuid $assignedTo,
        public readonly string $assignedToType,
        public readonly string $taskName,
        public readonly ?\DateTimeImmutable $deadline = null,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.task_assigned';
    }

    public function getAggregateId(): Uuid
    {
        return $this->taskId;
    }
}
