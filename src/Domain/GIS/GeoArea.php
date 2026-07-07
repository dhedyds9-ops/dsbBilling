<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoBoundingBox;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoArea extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public GeoPolygon $polygon,
        public readonly ?float $areaMetersSquared = null,
        public readonly ?GeoBoundingBox $boundingBox = null,
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
        GeoPolygon $polygon,
        ?float $areaMetersSquared = null,
        ?GeoBoundingBox $boundingBox = null,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $polygon,
            $areaMetersSquared,
            $boundingBox,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function contains(Coordinate $point): bool {
        $vertices = $this->polygon->vertices;
        $x = $point->longitude->value;
        $y = $point->latitude->value;
        
        $inside = false;
        $j = count($vertices) - 1;

        for ($i = 0; $i < count($vertices); $i++) {
            $xi = $vertices[$i]->longitude->value;
            $yi = $vertices[$i]->latitude->value;
            $xj = $vertices[$j]->longitude->value;
            $yj = $vertices[$j]->latitude->value;

            if (
                (($yi > $y) !== ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)
            ) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }

    public function toGeoJson(): array {
        return [
            'type' => 'Feature',
            'geometry' => $this->polygon->toGeoJson()['geometry'],
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'area_meters_squared' => $this->areaMetersSquared,
                'bounding_box' => $this->boundingBox?->toArray(),
                'metadata' => $this->metadata,
            ],
        ];
    }
}
