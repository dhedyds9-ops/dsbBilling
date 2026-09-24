<?php

namespace App\Services\Adapters\Maps;

use Illuminate\Support\Facades\Http;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class GoogleMapsAdapter implements MapsAdapterInterface
{
    public function __construct(protected string $apiKey)
    {
    }

    public function getDistance(GPSCoordinate $start, GPSCoordinate $end): float
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => "{$start->latitude},{$start->longitude}",
            'destinations' => "{$end->latitude},{$end->longitude}",
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && isset($response['rows'][0]['elements'][0]['distance']['value'])) {
            return (float)$response['rows'][0]['elements'][0]['distance']['value'];
        }

        return $this->calculateHaversineDistance($start, $end);
    }

    public function getTravelTime(GPSCoordinate $start, GPSCoordinate $end): int
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => "{$start->latitude},{$start->longitude}",
            'destinations' => "{$end->latitude},{$end->longitude}",
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && isset($response['rows'][0]['elements'][0]['duration']['value'])) {
            return (int)$response['rows'][0]['elements'][0]['duration']['value'];
        }

        $distance = $this->calculateHaversineDistance($start, $end);
        return (int)($distance / 1000 / 50 * 3600);
    }

    public function geocode(string $address): ?GPSCoordinate
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && isset($response['results'][0]['geometry']['location'])) {
            return new GPSCoordinate(
                $response['results'][0]['geometry']['location']['lat'],
                $response['results'][0]['geometry']['location']['lng']
            );
        }

        return null;
    }

    public function reverseGeocode(GPSCoordinate $coordinate): ?string
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$coordinate->latitude},{$coordinate->longitude}",
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && isset($response['results'][0]['formatted_address'])) {
            return $response['results'][0]['formatted_address'];
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
