<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class CheckedInEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly GPSCoordinate $location,
        public readonly \DateTimeImmutable $checkedInAt,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        GPSCoordinate $location,
        \DateTimeImmutable $checkedInAt,
    ): self {
        return new self(
            $technicianId,
            $location,
            $checkedInAt,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
