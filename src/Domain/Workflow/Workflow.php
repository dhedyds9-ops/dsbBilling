<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\WorkflowStatus;
use Src\Domain\Workflow\Enums\TriggerType;
use Src\Domain\Workflow\ValueObjects\WorkflowContext;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Workflow extends AggregateRoot
{
    private WorkflowStatus $status;
    private array $nodes = [];
    private array $transitions = [];
    private ?Uuid $startNodeId = null;
    private ?Uuid $endNodeId = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $entityType,
        public readonly TriggerType $triggerType,
        public readonly WorkflowContext $initialContext,
        public readonly int $version = 1,
        public readonly ?Uuid $previousVersionId = null,
        public readonly ?Uuid $parentWorkflowId = null,
        public readonly ?DateTimeImmutable $effectiveFrom = null,
        public readonly ?DateTimeImmutable $effectiveTo = null,
        public readonly ?string $module = null,
        public readonly ?array $metadata = []
    ) {
        $this->status = WorkflowStatus::DRAFT;
    }

    public static function create(
        string $name,
        string $description,
        string $entityType,
        TriggerType $triggerType,
        ?string $module = null
    ): self {
        return new self(
            id: Uuid::generate(),
            name: $name,
            description: $description,
            entityType: $entityType,
            triggerType: $triggerType,
            initialContext: new WorkflowContext(),
            module: $module
        );
    }

    public function getStatus(): WorkflowStatus
    {
        return $this->status;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getTransitions(): array
    {
        return $this->transitions;
    }

    public function getStartNodeId(): ?Uuid
    {
        return $this->startNodeId;
    }

    public function getEndNodeId(): ?Uuid
    {
        return $this->endNodeId;
    }

    public function addNode(Uuid $nodeId, string $type, string $name, array $config = []): void
    {
        $this->nodes[$nodeId->value] = [
            'id' => $nodeId->value,
            'type' => $type,
            'name' => $name,
            'config' => $config,
        ];
    }

    public function addTransition(
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $type,
        ?array $conditions = null,
        ?array $actions = null
    ): void {
        $transitionId = Uuid::generate();
        $this->transitions[] = [
            'id' => $transitionId->value,
            'from_node_id' => $fromNodeId->value,
            'to_node_id' => $toNodeId->value,
            'type' => $type,
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }

    public function setStartNode(Uuid $nodeId): void
    {
        $this->startNodeId = $nodeId;
    }

    public function setEndNode(Uuid $nodeId): void
    {
        $this->endNodeId = $nodeId;
    }

    public function activate(): void
    {
        if ($this->status !== WorkflowStatus::DRAFT) {
            throw new \DomainException("Only draft workflows can be activated");
        }
        if ($this->startNodeId === null || $this->endNodeId === null) {
            throw new \DomainException("Workflow must have start and end nodes");
        }
        $this->status = WorkflowStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->status = WorkflowStatus::INACTIVE;
    }

    public function archive(): void
    {
        $this->status = WorkflowStatus::ARCHIVED;
    }

    public function createNewVersion(): Workflow
    {
        return new self(
            id: Uuid::generate(),
            name: $this->name,
            description: $this->description,
            entityType: $this->entityType,
            triggerType: $this->triggerType,
            initialContext: $this->initialContext,
            version: $this->version + 1,
            previousVersionId: $this->id,
            parentWorkflowId: $this->parentWorkflowId ?? $this->id,
            effectiveFrom: new DateTimeImmutable(),
            module: $this->module,
            metadata: $this->metadata
        );
    }

    public function isEffective(): bool
    {
        $now = new DateTimeImmutable();
        
        if ($this->effectiveFrom !== null && $now < $this->effectiveFrom) {
            return false;
        }
        
        if ($this->effectiveTo !== null && $now > $this->effectiveTo) {
            return false;
        }
        
        return $this->status === WorkflowStatus::ACTIVE;
    }
}
