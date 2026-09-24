<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RouteCompletedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly Uuid $routeHistoryId,
        public readonly float $distance,
        public readonly int $travelTime,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        Uuid $routeHistoryId,
        float $distance,
        int $travelTime,
    ): self {
        return new self(
            $technicianId,
            $routeHistoryId,
            $distance,
            $travelTime,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
