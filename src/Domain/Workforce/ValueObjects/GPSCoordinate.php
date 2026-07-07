<?php

namespace Src\Domain\Workforce\ValueObjects;

readonly class GPSCoordinate {
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?float $accuracy = null,
    ) {
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException("Latitude must be between -90 and 90");
        }
        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException("Longitude must be between -180 and 180");
        }
    }

    public function toArray(): array {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
        ];
    }
}
