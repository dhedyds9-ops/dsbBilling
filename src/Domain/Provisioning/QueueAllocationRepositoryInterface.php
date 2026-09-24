<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface QueueAllocationRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): ?QueueAllocation;
    public function findByDeviceId(Uuid $deviceId): array;
    public function findActiveByDeviceAndQueue(Uuid $deviceId, int $queueId): ?QueueAllocation;
    public function findAllActive(): array;
}
