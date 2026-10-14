<?php

namespace Src\Domain\Workflow;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class WorkflowHistory extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $instanceId,
        public readonly Uuid $nodeId,
        public readonly string $action,
        public readonly Uuid $performedBy,
        public readonly ?string $performedByType = null,
        public readonly ?array $previousState = null,
        public readonly ?array $newState = null,
        public readonly ?string $comment = null,
        public readonly ?array $metadata = null,
        public readonly DateTimeImmutable $performedAt
    ) {}

    public static function record(
        Uuid $instanceId,
        Uuid $nodeId,
        string $action,
        Uuid $performedBy,
        ?string $performedByType = null,
        ?array $previousState = null,
        ?array $newState = null,
        ?string $comment = null,
        ?array $metadata = null
    ): self {
        return new self(
            id: Uuid::generate(),
            instanceId: $instanceId,
            nodeId: $nodeId,
            action: $action,
            performedBy: $performedBy,
            performedByType: $performedByType,
            previousState: $previousState,
            newState: $newState,
            comment: $comment,
            metadata: $metadata,
            performedAt: new DateTimeImmutable()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'instance_id' => $this->instanceId->value,
            'node_id' => $this->nodeId->value,
            'action' => $this->action,
            'performed_by' => $this->performedBy->value,
            'performed_by_type' => $this->performedByType,
            'previous_state' => $this->previousState,
            'new_state' => $this->newState,
            'comment' => $this->comment,
            'metadata' => $this->metadata,
            'performed_at' => $this->performedAt->format('c'),
        ];
    }
}
