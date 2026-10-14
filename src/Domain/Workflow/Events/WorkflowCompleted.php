<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowCompleted implements DomainEvent
{
    public function __construct(
        public readonly Uuid $instanceId,
        public readonly Uuid $workflowId,
        public readonly string $result,
        public readonly int $durationMinutes,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.completed';
    }

    public function getAggregateId(): Uuid
    {
        return $this->instanceId;
    }
}
