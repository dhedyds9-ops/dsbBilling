<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RouteOptimizedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $technicianId,
        public readonly Uuid $routeOptimizationId,
        public readonly float $totalDistance,
        public readonly int $totalTime,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $technicianId,
        Uuid $routeOptimizationId,
        float $totalDistance,
        int $totalTime,
    ): self {
        return new self(
            $technicianId,
            $routeOptimizationId,
            $totalDistance,
            $totalTime,
            new \DateTimeImmutable()
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
