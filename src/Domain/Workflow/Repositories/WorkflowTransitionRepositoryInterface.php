<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowTransition;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowTransitionRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowTransition;
    public function findByWorkflow(Uuid $workflowId): array;
    public function findByFromNode(Uuid $workflowId, Uuid $fromNodeId): array;
    public function findByNode(Uuid $workflowId, Uuid $nodeId): array;
    public function save(WorkflowTransition $transition): void;
    public function delete(WorkflowTransition $transition): void;
}
