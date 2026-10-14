<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum PortType: string
{
    case PON = 'pon';
    case ETHERNET = 'ethernet';
    case SPLITTER = 'splitter';
}

class PortReservation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly PortType $portType,
        public readonly Uuid $deviceId,
        public readonly int $portNumber,
        public ReservationStatus $status = ReservationStatus::ACTIVE,
        public DateTimeImmutable $reservedAt,
        public ?DateTimeImmutable $expiresAt = null,
        public ?DateTimeImmutable $releasedAt = null
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        PortType $portType,
        Uuid $deviceId,
        int $portNumber,
        ?DateTimeImmutable $expiresAt = null
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $portType,
            $deviceId,
            $portNumber,
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
}
