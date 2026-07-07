<?php

namespace Src\Domain\GIS\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoAreaCalculated implements DomainEvent {
    public function __construct(
        public readonly Uuid $geoAreaId,
        public readonly float $areaMetersSquared,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $geoAreaId,
        float $areaMetersSquared,
    ): self {
        return new self(
            $geoAreaId,
            $areaMetersSquared,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
