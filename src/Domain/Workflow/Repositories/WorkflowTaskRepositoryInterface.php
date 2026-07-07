<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowTask;
use Src\Domain\Workflow\Enums\TaskStatus;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowTaskRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowTask;
    public function findByInstance(Uuid $instanceId): array;
    public function findByNode(Uuid $nodeId): array;
    public function findPending(?Uuid $assignedTo = null): array;
    public function findInProgress(?Uuid $assignedTo = null): array;
    public function findCompleted(?Uuid $assignedTo = null): array;
    public function findOverdue(): array;
    public function findDueSoon(int $hours = 24): array;
    public function findByAssignee(Uuid $assignedTo): array;
    public function save(WorkflowTask $task): void;
    public function delete(WorkflowTask $task): void;
}
