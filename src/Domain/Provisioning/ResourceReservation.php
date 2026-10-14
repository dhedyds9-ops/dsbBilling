<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ReservationStatus: string
{
    case ACTIVE = 'active';
    case ASSIGNED = 'assigned';
    case RELEASED = 'released';
    case EXPIRED = 'expired';
}

enum ResourceType: string
{
    case OLT_PORT = 'olt_port';
    case ONU = 'onu';
    case VLAN = 'vlan';
    case IP_POOL = 'ip_pool';
    case QUEUE = 'queue';
    case RADIUS_PROFILE = 'radius_profile';
    case FIBER_CORE = 'fiber_core';
    case SPLITTER_PORT = 'splitter_port';
}

class ResourceReservation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly ResourceType $resourceType,
        public readonly Uuid $resourceId,
        public ReservationStatus $status = ReservationStatus::ACTIVE,
        public DateTimeImmutable $reservedAt,
        public ?DateTimeImmutable $expiresAt = null,
        public ?DateTimeImmutable $releasedAt = null,
        public array $details = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        ResourceType $resourceType,
        Uuid $resourceId,
        ?DateTimeImmutable $expiresAt = null
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $resourceType,
            $resourceId,
            ReservationStatus::ACTIVE,
            new DateTimeImmutable(),
            $expiresAt
        );
    }

    public function markAssigned(): void
    {
        $this->status = ReservationStatus::ASSIGNED;
    }

    public function release(): void
    {
        $this->status = ReservationStatus::RELEASED;
        $this->releasedAt = new DateTimeImmutable();
    }

    public function expire(): void
    {
        $this->status = ReservationStatus::EXPIRED;
        $this->releasedAt = new DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && new DateTimeImmutable() > $this->expiresAt;
    }
}
