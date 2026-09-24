<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class CheckedOutEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly GPSCoordinate $location,
        public readonly \DateTimeImmutable $checkedOutAt,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        GPSCoordinate $location,
        \DateTimeImmutable $checkedOutAt,
    ): self {
        return new self(
            $technicianId,
            $location,
            $checkedOutAt,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
