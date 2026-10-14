<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface IPAllocationRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): ?IPAllocation;
    public function findByIpPoolId(Uuid $ipPoolId): array;
    public function findActiveByIpAddress(string $ipAddress): ?IPAllocation;
    public function findAllActive(): array;
}
