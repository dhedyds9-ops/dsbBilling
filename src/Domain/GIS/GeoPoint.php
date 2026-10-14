<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoPoint extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public Coordinate $coordinate,
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
        Coordinate $coordinate,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $coordinate,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function updateCoordinate(Coordinate $coordinate): void {
        $this->coordinate = $coordinate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toGeoJson(): array {
        return [
            'type' => 'Feature',
            'geometry' => $this->coordinate->toGeoJson(),
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'metadata' => $this->metadata,
            ],
        ];
    }
}
