<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\WorkflowInstanceStatus;
use Src\Domain\Workflow\ValueObjects\WorkflowContext;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class WorkflowInstance extends AggregateRoot
{
    private WorkflowInstanceStatus $status;
    private ?Uuid $currentNodeId = null;
    private array $executionPath = [];
    private array $contextData = [];
    private ?DateTimeImmutable $startedAt = null;
    private ?DateTimeImmutable $completedAt = null;
    private ?Uuid $parentInstanceId = null;
    private array $variables = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workflowId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly WorkflowContext $context,
        public readonly Uuid $initiatedBy,
        public readonly int $version = 1
    ) {
        $this->status = WorkflowInstanceStatus::PENDING;
        $this->contextData = $context->data;
    }

    public static function start(
        Workflow $workflow,
        string $entityId,
        WorkflowContext $context,
        Uuid $initiatedBy
    ): self {
        $instance = new self(
            id: Uuid::generate(),
            workflowId: $workflow->id,
            entityType: $workflow->entityType,
            entityId: $entityId,
            context: $context,
            initiatedBy: $initiatedBy
        );
        
        $instance->status = WorkflowInstanceStatus::RUNNING;
        $instance->startedAt = new DateTimeImmutable();
        $instance->currentNodeId = $workflow->getStartNodeId();
        $instance->executionPath[] = [
            'node_id' => $workflow->getStartNodeId()?->value,
            'entered_at' => $instance->startedAt->format('c'),
            'action' => 'started'
        ];
        
        return $instance;
    }

    public function getStatus(): WorkflowInstanceStatus
    {
        return $this->status;
    }

    public function getCurrentNodeId(): ?Uuid
    {
        return $this->currentNodeId;
    }

    public function getExecutionPath(): array
    {
        return $this->executionPath;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getContextData(string $key, mixed $default = null): mixed
    {
        return $this->contextData[$key] ?? $default;
    }

    public function setVariable(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }

    public function updateContext(string $key, mixed $value): void
    {
        $this->contextData[$key] = $value;
    }

    public function moveToNode(Uuid $nodeId, string $action = 'transition'): void
    {
        $this->currentNodeId = $nodeId;
        $this->executionPath[] = [
            'node_id' => $nodeId->value,
            'entered_at' => (new DateTimeImmutable())->format('c'),
            'action' => $action
        ];
    }

    public function complete(): void
    {
        $this->status = WorkflowInstanceStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->executionPath[] = [
            'node_id' => null,
            'entered_at' => $this->completedAt->format('c'),
            'action' => 'completed'
        ];
    }

    public function cancel(string $reason): void
    {
        $this->status = WorkflowInstanceStatus::CANCELLED;
        $this->completedAt = new DateTimeImmutable();
        $this->executionPath[] = [
            'node_id' => null,
            'entered_at' => $this->completedAt->format('c'),
            'action' => 'cancelled',
            'reason' => $reason
        ];
    }

    public function fail(string $error): void
    {
        $this->status = WorkflowInstanceStatus::FAILED;
        $this->completedAt = new DateTimeImmutable();
        $this->executionPath[] = [
            'node_id' => null,
            'entered_at' => $this->completedAt->format('c'),
            'action' => 'failed',
            'error' => $error
        ];
    }

    public function escalate(): void
    {
        $this->status = WorkflowInstanceStatus::ESCALATED;
    }

    public function wait(): void
    {
        $this->status = WorkflowInstanceStatus::WAITING;
    }

    public function resume(): void
    {
        $this->status = WorkflowInstanceStatus::RUNNING;
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function getDurationMinutes(): int
    {
        $end = $this->completedAt ?? new DateTimeImmutable();
        return $this->startedAt->diff($end)->i;
    }

    public function addParallelInstance(Uuid $instanceId): void
    {
        $this->executionPath[count($this->executionPath) - 1]['parallel_instances'][] = $instanceId->value;
    }

    public function rollback(Uuid $toNodeId): void
    {
        $this->currentNodeId = $toNodeId;
        $this->executionPath[] = [
            'node_id' => $toNodeId->value,
            'entered_at' => (new DateTimeImmutable())->format('c'),
            'action' => 'rollback'
        ];
        $this->status = WorkflowInstanceStatus::RUNNING;
    }
}
