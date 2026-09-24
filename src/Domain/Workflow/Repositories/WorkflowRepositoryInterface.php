<?php

namespace Src\Domain\Workflow\Repositories;

use Src\Domain\Workflow\Workflow;
use Src\Domain\Workflow\Enums\TriggerType;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WorkflowRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?Workflow;
    public function findByEntityType(string $entityType): array;
    public function findByTrigger(TriggerType $triggerType): array;
    public function findActive(?string $module = null): array;
    public function findByModule(string $module): array;
    public function findVersions(Uuid $parentWorkflowId): array;
    public function save(Workflow $workflow): void;
    public function delete(Workflow $workflow): void;
}
