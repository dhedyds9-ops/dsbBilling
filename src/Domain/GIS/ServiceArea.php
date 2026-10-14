<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\Enums\CoordinateSystem;

class ServiceArea extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public GeoPoint $centerPoint,
        public GeoDistance $radius,
        public readonly ?GeoArea $serviceArea = null,
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
        GeoPoint $centerPoint,
        GeoDistance $radius,
        ?GeoArea $serviceArea = null,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $centerPoint,
            $radius,
            $serviceArea,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function contains(Coordinate $point): bool {
        if ($this->serviceArea) {
            return $this->serviceArea->contains($point);
        }

        $distance = $this->calculateDistance($this->centerPoint->coordinate, $point);
        return $distance <= $this->radius->meters;
    }

    private function calculateDistance(Coordinate $pointA, Coordinate $pointB): float {
        $earthRadius = 6371000;

        $lat1 = $pointA->latitude->toRadians();
        $lat2 = $pointB->latitude->toRadians();
        $deltaLat = deg2rad($pointB->latitude->value - $pointA->latitude->value);
        $deltaLon = deg2rad($pointB->longitude->value - $pointA->longitude->value);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function toGeoJson(): array {
        if ($this->serviceArea) {
            return $this->serviceArea->toGeoJson();
        }

        $radiusKm = $this->radius->toKilometers();
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->centerPoint->coordinate->longitude->value, $this->centerPoint->coordinate->latitude->value],
            ],
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'radius' => $this->radius->meters,
                'metadata' => $this->metadata,
            ],
        ];
    }
}
