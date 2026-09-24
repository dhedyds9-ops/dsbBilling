<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface ResourceAssignmentRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): array;
    public function findByResource(ResourceType $resourceType, Uuid $resourceId): array;
    public function findActiveByResource(ResourceType $resourceType, Uuid $resourceId): ?ResourceAssignment;
    public function findAllActive(): array;
}
