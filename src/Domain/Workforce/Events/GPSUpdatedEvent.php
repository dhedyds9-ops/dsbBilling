<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class GPSUpdatedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly GPSCoordinate $coordinate,
        public readonly ?float $speed,
        public readonly ?float $heading,
        public readonly ?float $altitude,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        GPSCoordinate $coordinate,
        ?float $speed,
        ?float $heading,
        ?float $altitude,
    ): self {
        return new self(
            $technicianId,
            $coordinate,
            $speed,
            $heading,
            $altitude,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
