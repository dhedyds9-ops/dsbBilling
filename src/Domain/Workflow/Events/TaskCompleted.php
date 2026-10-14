<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TaskCompleted implements DomainEvent
{
    public function __construct(
        public readonly Uuid $taskId,
        public readonly Uuid $instanceId,
        public readonly Uuid $completedBy,
        public readonly string $taskName,
        public readonly array $outputs = [],
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.task_completed';
    }

    public function getAggregateId(): Uuid
    {
        return $this->taskId;
    }
}
