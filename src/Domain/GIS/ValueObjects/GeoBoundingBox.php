<?php

namespace Src\Domain\GIS\ValueObjects;

readonly class GeoBoundingBox {
    public function __construct(
        public Coordinate $southWest,
        public Coordinate $northEast,
    ) {}

    public static function fromCoordinates(
        float $minLat,
        float $minLon,
        float $maxLat,
        float $maxLon
    ): self {
        return new self(
            Coordinate::fromFloat($minLat, $minLon),
            Coordinate::fromFloat($maxLat, $maxLon)
        );
    }

    public function contains(Coordinate $point): bool {
        return (
            $point->latitude->value >= $this->southWest->latitude->value &&
            $point->latitude->value <= $this->northEast->latitude->value &&
            $point->longitude->value >= $this->southWest->longitude->value &&
            $point->longitude->value <= $this->northEast->longitude->value
        );
    }

    public function toArray(): array {
        return [
            'south_west' => $this->southWest->toArray(),
            'north_east' => $this->northEast->toArray(),
        ];
    }

    public function toGeoJson(): array {
        return [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [$this->southWest->longitude->value, $this->southWest->latitude->value],
                    [$this->southWest->longitude->value, $this->northEast->latitude->value],
                    [$this->northEast->longitude->value, $this->northEast->latitude->value],
                    [$this->northEast->longitude->value, $this->southWest->latitude->value],
                    [$this->southWest->longitude->value, $this->southWest->latitude->value],
                ]
            ],
        ];
    }
}
