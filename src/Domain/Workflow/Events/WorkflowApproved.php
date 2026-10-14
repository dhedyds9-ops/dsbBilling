<?php

namespace Src\Domain\Workflow\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowApproved implements DomainEvent
{
    public function __construct(
        public readonly Uuid $instanceId,
        public readonly Uuid $workflowId,
        public readonly Uuid $approvedBy,
        public readonly string $decision,
        public readonly ?string $comment = null,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'workflow.approved';
    }

    public function getAggregateId(): Uuid
    {
        return $this->instanceId;
    }
}
