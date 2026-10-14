<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowInstance;
use Src\Domain\Workflow\Enums\WorkflowInstanceStatus;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowInstanceRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowInstance;
    public function findByWorkflow(Uuid $workflowId): array;
    public function findByEntity(string $entityType, string $entityId): array;
    public function findActive(?string $entityType = null): array;
    public function findPending(): array;
    public function findInProgress(): array;
    public function findCompleted(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function findByInitiator(Uuid $initiatedBy): array;
    public function findOverdue(): array;
    public function save(WorkflowInstance $instance): void;
    public function delete(WorkflowInstance $instance): void;
}
