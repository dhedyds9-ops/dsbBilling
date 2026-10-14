<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowApproval;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowApprovalRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowApproval;
    public function findByTask(Uuid $taskId): ?WorkflowApproval;
    public function findByInstance(Uuid $instanceId): array;
    public function findPending(?Uuid $approverId = null): array;
    public function findOverdue(): array;
    public function findByStatus(string $status): array;
    public function save(WorkflowApproval $approval): void;
    public function delete(WorkflowApproval $approval): void;
}
