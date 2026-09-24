<?php

namespace App\Services\GIS;

use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\GeoPolygon;

class GeoCalculationService
{
    public function calculatePolygonArea(GeoPolygon $polygon): float
    {
        $vertices = $polygon->vertices;
        $n = count($vertices);
        if ($n < 3) {
            return 0;
        }

        $area = 0;
        $earthRadius = 6371000;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $lat1 = $vertices[$i]->latitude->toRadians();
            $lon1 = $vertices[$i]->longitude->toRadians();
            $lat2 = $vertices[$j]->latitude->toRadians();
            $lon2 = $vertices[$j]->longitude->toRadians();

            $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        $area = abs($area * $earthRadius * $earthRadius / 2);

        return $area;
    }

    public function pointInPolygon(Coordinate $point, GeoPolygon $polygon): bool
    {
        $vertices = $polygon->vertices;
        $x = $point->longitude->value;
        $y = $point->latitude->value;
        $inside = false;

        for ($i = 0, $j = count($vertices) - 1; $i < count($vertices); $j = $i++) {
            $xi = $vertices[$i]->longitude->value;
            $yi = $vertices[$i]->latitude->value;
            $xj = $vertices[$j]->longitude->value;
            $yj = $vertices[$j]->latitude->value;

            $intersect = (($yi > $y) !== ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
