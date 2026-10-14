<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowStarted implements DomainEvent
{
    public function __construct(
        public readonly Uuid $instanceId,
        public readonly Uuid $workflowId,
        public readonly Uuid $initiatedBy,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.started';
    }

    public function getAggregateId(): Uuid
    {
        return $this->instanceId;
    }
}
