<?php

namespace App\Integration\Maps\Contracts;

use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

interface MapsAdapterInterface
{
    public function getDistance(GPSCoordinate $start, GPSCoordinate $end): float;
    public function getTravelTime(GPSCoordinate $start, GPSCoordinate $end): int;
    public function geocode(string $address): ?GPSCoordinate;
    public function reverseGeocode(GPSCoordinate $coordinate): ?string;
}
