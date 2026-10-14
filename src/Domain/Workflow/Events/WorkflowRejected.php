<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowRejected implements DomainEvent
{
    public function __construct(
        public readonly Uuid $instanceId,
        public readonly Uuid $workflowId,
        public readonly Uuid $rejectedBy,
        public readonly string $reason,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.rejected';
    }

    public function getAggregateId(): Uuid
    {
        return $this->instanceId;
    }
}
