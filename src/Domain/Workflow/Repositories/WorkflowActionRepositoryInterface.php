<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowAction;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowActionRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowAction;
    public function findByWorkflow(Uuid $workflowId): array;
    public function findByNode(Uuid $nodeId): array;
    public function findByType(string $type): array;
    public function save(WorkflowAction $action): void;
    public function delete(WorkflowAction $action): void;
}
