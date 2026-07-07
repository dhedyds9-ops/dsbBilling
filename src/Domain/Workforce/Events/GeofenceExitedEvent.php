<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeofenceExitedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly Uuid $geofenceId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        Uuid $geofenceId,
    ): self {
        return new self(
            $technicianId,
            $geofenceId,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
