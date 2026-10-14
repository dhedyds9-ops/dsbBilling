<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoPath extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        /** @var array<array<Coordinate>> */
        public array $paths,
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
        array $paths,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $paths,
            $coordinateSystem,
            $name,
            $description,
            $metadata,
        );
    }

    public function toGeoJson(): array {
        $coordinates = [];
        foreach ($this->paths as $path) {
            $coordinates[] = array_map(fn(Coordinate $c) => [$c->longitude->value, $c->latitude->value], $path);
        }
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'MultiLineString',
                'coordinates' => $coordinates,
            ],
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'metadata' => $this->metadata,
            ],
        ];
    }
}
