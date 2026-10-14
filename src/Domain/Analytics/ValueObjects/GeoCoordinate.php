<?php

namespace Src\Domain\Analytics\ValueObjects;

final class GeoCoordinate
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {}

    public function distanceTo(GeoCoordinate $other): float
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

    public function equals(GeoCoordinate $other): bool
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

    public function toBoundingBox(float $radiusKm): array
    {
        $latDelta = $radiusKm / 111.0;
        $lonDelta = $radiusKm / (111.0 * cos(deg2rad($this->latitude)));

        return [
            'min_lat' => $this->latitude - $latDelta,
            'max_lat' => $this->latitude + $latDelta,
            'min_lon' => $this->longitude - $lonDelta,
            'max_lon' => $this->longitude + $lonDelta,
        ];
    }
}
