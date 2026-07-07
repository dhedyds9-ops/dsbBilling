<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\Enums\CoverageStatus;
use Src\Domain\GIS\Enums\CoordinateSystem;

class CoverageArea extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public GeoArea $area,
        public readonly Uuid $parentId,
        public CoverageStatus $status,
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
        GeoArea $area,
        Uuid $parentId,
        CoverageStatus $status,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $area,
            $parentId,
            $status,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function updateStatus(CoverageStatus $status): void {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateArea(GeoArea $area): void {
        $this->area = $area;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toGeoJson(): array {
        $geoJson = $this->area->toGeoJson();
        $geoJson['properties'] = array_merge($geoJson['properties'], [
            'status' => $this->status->value,
            'name' => $this->name,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ]);
        return $geoJson;
    }
}
