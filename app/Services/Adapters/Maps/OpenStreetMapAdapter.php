<?php

namespace App\Services\Adapters\Maps;

use Illuminate\Support\Facades\Http;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class OpenStreetMapAdapter implements MapsAdapterInterface
{
    public function getDistance(GPSCoordinate $start, GPSCoordinate $end): float
    {
        // OSRM (Open Source Routing Machine) is typically used with OpenStreetMap
        $response = Http::get("http://router.project-osrm.org/route/v1/driving/{$start->longitude},{$start->latitude};{$end->longitude},{$end->latitude}", [
            'overview' => 'false',
        ]);

        if ($response->successful() && isset($response['routes'][0]['distance'])) {
            return (float)$response['routes'][0]['distance'];
        }

        return $this->calculateHaversineDistance($start, $end);
    }

    public function getTravelTime(GPSCoordinate $start, GPSCoordinate $end): int
    {
        $response = Http::get("http://router.project-osrm.org/route/v1/driving/{$start->longitude},{$start->latitude};{$end->longitude},{$end->latitude}", [
            'overview' => 'false',
        ]);

        if ($response->successful() && isset($response['routes'][0]['duration'])) {
            return (int)$response['routes'][0]['duration'];
        }

        $distance = $this->calculateHaversineDistance($start, $end);
        return (int)($distance / 1000 / 50 * 3600);
    }

    public function geocode(string $address): ?GPSCoordinate
    {
        $response = Http::get('https://nominatim.openstreetmap.org/search', [
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
        ]);

        if ($response->successful() && isset($response[0])) {
            return new GPSCoordinate(
                (float)$response[0]['lat'],
                (float)$response[0]['lon']
            );
        }

        return null;
    }

    public function reverseGeocode(GPSCoordinate $coordinate): ?string
    {
        $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
            'lat' => $coordinate->latitude,
            'lon' => $coordinate->longitude,
            'format' => 'json',
        ]);

        if ($response->successful() && isset($response['display_name'])) {
            return $response['display_name'];
        }

        return null;
    }

    private function calculateHaversineDistance(GPSCoordinate $start, GPSCoordinate $end): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($end->latitude - $start->latitude);
        $dLon = deg2rad($end->longitude - $start->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($start->latitude)) * cos(deg2rad($end->latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
