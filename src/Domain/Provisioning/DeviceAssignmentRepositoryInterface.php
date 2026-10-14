<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface DeviceAssignmentRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): array;
    public function findByDevice(DeviceType $deviceType, Uuid $deviceId): array;
    public function findActiveByDevice(DeviceType $deviceType, Uuid $deviceId): ?DeviceAssignment;
    public function findAllActive(): array;
}
