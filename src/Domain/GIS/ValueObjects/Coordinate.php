<?php

namespace Src\Domain\GIS\ValueObjects;

readonly class Coordinate {
    public function __construct(
        public Latitude $latitude,
        public Longitude $longitude,
    ) {}

    public static function fromFloat(float $lat, float $lon): self {
        return new self(
            new Latitude($lat),
            new Longitude($lon)
        );
    }

    public function toArray(): array {
        return [
            'latitude' => $this->latitude->value,
            'longitude' => $this->longitude->value,
        ];
    }

    public function toGeoJson(): array {
        return [
            'type' => 'Point',
            'coordinates' => [$this->longitude->value, $this->latitude->value],
        ];
    }

    public function __toString(): string {
        return "{$this->latitude->value}, {$this->longitude->value}";
    }
}
