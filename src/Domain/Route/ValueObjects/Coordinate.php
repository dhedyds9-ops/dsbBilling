<?php

namespace Src\Domain\Route\ValueObjects;

final class Coordinate
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {}

    public function distanceTo(Coordinate $other): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    public function equals(Coordinate $other): bool
    {
        return abs($this->latitude - $other->latitude) < 0.0001 &&
               abs($this->longitude - $other->longitude) < 0.0001;
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['latitude'],
            (float) $data['longitude']
        );
    }
}
