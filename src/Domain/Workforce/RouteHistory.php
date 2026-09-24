<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class RouteHistory extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $technicianId,
        public readonly Uuid $assignmentId,
        public readonly GPSCoordinate $startLocation,
        public readonly GPSCoordinate $endLocation,
        public readonly float $distance, // in meters
        public readonly int $travelTime, // in seconds
        public readonly DateTimeImmutable $startedAt,
        public readonly ?DateTimeImmutable $endedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        /** @var GPSCoordinate[] */
        public array $waypoints = [],
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $technicianId,
        Uuid $assignmentId,
        GPSCoordinate $startLocation,
        GPSCoordinate $endLocation,
        float $distance,
        int $travelTime,
        DateTimeImmutable $startedAt,
    ): self {
        return new self(
            Uuid::random(),
            $technicianId,
            $assignmentId,
            $startLocation,
            $endLocation,
            $distance,
            $travelTime,
            $startedAt
        );
    }

    public function addWaypoint(GPSCoordinate $waypoint): void {
        $this->waypoints[] = $waypoint;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function endRoute(DateTimeImmutable $endedAt, float $actualDistance, int $actualTravelTime): void {
        $this->endedAt = $endedAt;
        $this->distance = $actualDistance;
        $this->travelTime = $actualTravelTime;
        $this->updatedAt = new DateTimeImmutable();
    }
}
