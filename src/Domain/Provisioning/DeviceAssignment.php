<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum DeviceType: string
{
    case OLT = 'olt';
    case ONU = 'onu';
    case ROUTER = 'router';
    case SWITCH = 'switch';
}

class DeviceAssignment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly DeviceType $deviceType,
        public readonly Uuid $deviceId,
        public bool $active = true,
        public DateTimeImmutable $assignedAt,
        public ?DateTimeImmutable $revokedAt = null
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        DeviceType $deviceType,
        Uuid $deviceId
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $deviceType,
            $deviceId,
            true,
            new DateTimeImmutable()
        );
    }

    public function revoke(): void
    {
        $this->active = false;
        $this->revokedAt = new DateTimeImmutable();
    }
}
