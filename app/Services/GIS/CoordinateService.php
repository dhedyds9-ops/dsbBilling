<?php

namespace App\Services\GIS;

use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\ValueObjects\GeoBoundingBox;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;

class CoordinateService
{
    public function validateCoordinate(Coordinate $coordinate): bool
    {
        return true;
    }

    public function calculateDistance(Coordinate $point1, Coordinate $point2): GeoDistance
    {
        $earthRadius = 6371000;

        $lat1 = $point1->latitude->toRadians();
        $lat2 = $point2->latitude->toRadians();
        $deltaLat = deg2rad($point2->latitude->value - $point1->latitude->value);
        $deltaLon = deg2rad($point2->longitude->value - $point1->longitude->value);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return GeoDistance::fromMeters($distance);
    }

    public function calculateBoundingBox(Coordinate $point, GeoDistance $radius): GeoBoundingBox
    {
        $earthRadius = 6371000;
        $angularRadius = $radius->meters / $earthRadius;

        $minLat = $point->latitude->value - rad2deg($angularRadius);
        $maxLat = $point->latitude->value + rad2deg($angularRadius);

        $deltaLon = rad2deg(asin(sin($angularRadius) / cos($point->latitude->toRadians())));

        $minLon = $point->longitude->value - $deltaLon;
        $maxLon = $point->longitude->value + $deltaLon;

        return GeoBoundingBox::fromCoordinates($minLat, $minLon, $maxLat, $maxLon);
    }
}
