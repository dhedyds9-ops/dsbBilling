<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class RouteOptimization extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $technicianId,
        public readonly GPSCoordinate $startLocation,
        public readonly GPSCoordinate $endLocation,
        /** @var GPSCoordinate[] */
        public array $stops,
        public readonly float $totalDistance,
        public readonly int $totalTime,
        public readonly string $optimizationMethod,
        public readonly DateTimeImmutable $calculatedAt,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $technicianId,
        GPSCoordinate $startLocation,
        GPSCoordinate $endLocation,
        array $stops,
        float $totalDistance,
        int $totalTime,
        string $optimizationMethod = 'nearest_neighbor',
    ): self {
        return new self(
            Uuid::random(),
            $technicianId,
            $startLocation,
            $endLocation,
            $stops,
            $totalDistance,
            $totalTime,
            $optimizationMethod,
            new DateTimeImmutable()
        );
    }
}
