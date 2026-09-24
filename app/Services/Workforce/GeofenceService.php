<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Geofence;
use Illuminate\Support\Facades\DB;
use Src\Domain\Workforce\Geofence as DomainGeofence;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\GeofenceType;

class GeofenceService
{
    public function createCircleGeofence(string $name, GPSCoordinate $center, float $radius, string $description = ''): DomainGeofence
    {
        return DB::transaction(function () use ($name, $center, $radius, $description) {
            $domainGeofence = DomainGeofence::createCircle($name, $center, $radius, $description);

            Geofence::create([
                'uuid' => $domainGeofence->id->value,
                'name' => $domainGeofence->name,
                'type' => $domainGeofence->type,
                'center_latitude' => $domainGeofence->center->latitude,
                'center_longitude' => $domainGeofence->center->longitude,
                'radius' => $domainGeofence->radius,
                'description' => $domainGeofence->description,
                'is_active' => $domainGeofence->isActive,
            ]);

            return $domainGeofence;
        });
    }

    public function isInsideGeofence(Uuid $geofenceId, GPSCoordinate $coordinate): bool
    {
        $geofence = Geofence::find($geofenceId->value);
        if (!$geofence || !$geofence->is_active) {
            return false;
        }

        switch ($geofence->type) {
            case GeofenceType::CIRCLE:
                return $this->isInsideCircle(
                    $geofence->center_latitude,
                    $geofence->center_longitude,
                    $geofence->radius,
                    $coordinate
                );
            case GeofenceType::POLYGON:
                return $this->isInsidePolygon($geofence->vertices, $coordinate);
            default:
                return false;
        }
    }

    private function isInsideCircle(float $centerLat, float $centerLon, float $radius, GPSCoordinate $coordinate): bool
    {
        $distance = $this->calculateDistance($centerLat, $centerLon, $coordinate->latitude, $coordinate->longitude);
        return $distance <= $radius;
    }

    private function isInsidePolygon(array $vertices, GPSCoordinate $coordinate): bool
    {
        $inside = false;
        $count = count($vertices);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $vertices[$i]['latitude'];
            $yi = $vertices[$i]['longitude'];
            $xj = $vertices[$j]['latitude'];
            $yj = $vertices[$j]['longitude'];

            if (
                (($yi > $coordinate->longitude) !== ($yj > $coordinate->longitude)) &&
                ($coordinate->latitude < ($xj - $xi) * ($coordinate->longitude - $yi) / ($yj - $yi) + $xi)
            ) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
