<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CapacityManagementRepositoryInterface extends RepositoryInterface
{
    public function findByResource(ResourceType $resourceType, Uuid $resourceId): ?CapacityManagement;
    public function findAllByResourceType(ResourceType $resourceType): array;
}
