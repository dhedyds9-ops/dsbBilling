<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoRoute extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        /** @var array<Coordinate> */
        public array $points,
        public GeoDistance $totalDistance,
        public readonly ?int $totalTimeSeconds = null,
        public CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
        public ?string $name = null,
        public ?string $description = null,
        public ?array $metadata = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        array $points,
        GeoDistance $totalDistance,
        ?int $totalTimeSeconds = null,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $points,
            $totalDistance,
            $totalTimeSeconds,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function addPoint(Coordinate $point): void {
        $this->points[] = $point;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toGeoJson(): array {
        $coordinates = array_map(fn(Coordinate $c) => [$c->longitude->value, $c->latitude->value], $this->points);
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $coordinates,
            ],
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'total_distance' => $this->totalDistance->meters,
                'total_time_seconds' => $this->totalTimeSeconds,
                'metadata' => $this->metadata,
            ],
        ];
    }
}
