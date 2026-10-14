<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface PortReservationRepositoryInterface extends RepositoryInterface
{
    public function findByServiceInstanceId(Uuid $serviceInstanceId): array;
    public function findByDeviceAndPort(PortType $portType, Uuid $deviceId, int $portNumber): ?PortReservation;
    public function findActiveByDevice(PortType $portType, Uuid $deviceId): array;
    public function findAllActive(): array;
}
