<?php

namespace Src\Domain\GIS\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoRouteCreated implements DomainEvent {
    public function __construct(
        public readonly Uuid $geoRouteId,
        public readonly float $totalDistanceMeters,
        public readonly ?int $totalTimeSeconds,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $geoRouteId,
        float $totalDistanceMeters,
        ?int $totalTimeSeconds = null,
    ): self {
        return new self(
            $geoRouteId,
            $totalDistanceMeters,
            $totalTimeSeconds,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
