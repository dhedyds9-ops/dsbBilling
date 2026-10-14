<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface VLANAllocationRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): ?VLANAllocation;
    public function findByVlanId(Uuid $vlanId): array;
    public function findActiveByVlanTag(int $vlanTag): ?VLANAllocation;
    public function findAllActive(): array;
}
