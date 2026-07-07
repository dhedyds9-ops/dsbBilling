<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\TransitionType;
use Src\Domain\Workflow\ValueObjects\TransitionCondition;
use Src\Domain\Workflow\ValueObjects\ApprovalConfig;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowTransition extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workflowId,
        public readonly Uuid $fromNodeId,
        public readonly Uuid $toNodeId,
        public readonly TransitionType $type,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?int $priority = 0,
        public readonly ?TransitionCondition $condition = null,
        public readonly ?ApprovalConfig $approvalConfig = null,
        public readonly ?array $actions = null,
        public readonly ?Uuid $rollbackToNodeId = null,
        public readonly bool $isDefault = false
    ) {}

    public static function createAutomatic(
        Uuid $workflowId,
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $name,
        ?TransitionCondition $condition = null
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            fromNodeId: $fromNodeId,
            toNodeId: $toNodeId,
            type: TransitionType::AUTOMATIC,
            name: $name,
            condition: $condition
        );
    }

    public static function createApproval(
        Uuid $workflowId,
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $name,
        ApprovalConfig $approvalConfig,
        ?TransitionCondition $condition = null,
        ?Uuid $rejectedNodeId = null
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            fromNodeId: $fromNodeId,
            toNodeId: $toNodeId,
            type: TransitionType::APPROVAL,
            name: $name,
            condition: $condition,
            approvalConfig: $approvalConfig
        );
    }

    public static function createConditional(
        Uuid $workflowId,
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $name,
        TransitionCondition $condition
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            fromNodeId: $fromNodeId,
            toNodeId: $toNodeId,
            type: TransitionType::CONDITIONAL,
            name: $name,
            condition: $condition
        );
    }

    public static function createParallel(
        Uuid $workflowId,
        array $fromNodeIds,
        array $toNodeIds,
        string $name
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            fromNodeId: $fromNodeIds[0] ?? Uuid::generate(),
            toNodeId: $toNodeIds[0] ?? Uuid::generate(),
            type: TransitionType::PARALLEL,
            name: $name,
            metadata: ['parallel_from_nodes' => $fromNodeIds, 'parallel_to_nodes' => $toNodeIds]
        );
    }

    public static function createRollback(
        Uuid $workflowId,
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $name,
        ?string $rollbackReason = null
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            fromNodeId: $fromNodeId,
            toNodeId: $toNodeId,
            type: TransitionType::ROLLBACK,
            name: $name,
            metadata: ['rollback_reason' => $rollbackReason]
        );
    }

    public function requiresApproval(): bool
    {
        return $this->type === TransitionType::APPROVAL;
    }

    public function isAutomatic(): bool
    {
        return $this->type === TransitionType::AUTOMATIC;
    }

    public function isConditional(): bool
    {
        return $this->type === TransitionType::CONDITIONAL;
    }

    public function isRollback(): bool
    {
        return $this->type === TransitionType::ROLLBACK;
    }

    public function evaluateCondition(mixed $context): bool
    {
        if ($this->condition === null) {
            return true;
        }
        return $this->condition->evaluate($context);
    }

    public function canTransition(array $context = []): bool
    {
        if ($this->isDefault) {
            return true;
        }
        return $this->evaluateCondition($context);
    }
}
