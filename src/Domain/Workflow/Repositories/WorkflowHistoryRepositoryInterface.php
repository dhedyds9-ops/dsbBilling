<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\WorkflowHistory;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowHistoryRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WorkflowHistory;
    public function findByInstance(Uuid $instanceId): array;
    public function findByNode(Uuid $instanceId, Uuid $nodeId): array;
    public function findByActor(Uuid $actorId): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(WorkflowHistory $history): void;
    public function delete(WorkflowHistory $history): void;
}
