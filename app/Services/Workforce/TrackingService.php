<?php

namespace App\Services\Workforce;

use App\Models\Workforce\GPSHistory;
use App\Models\Workforce\RouteHistory;
use Illuminate\Support\Facades\DB;
use Src\Domain\Workforce\GPSHistory as DomainGPSHistory;
use Src\Domain\Workforce\RouteHistory as DomainRouteHistory;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TrackingService
{
    public function logGPSLocation(Uuid $technicianId, GPSCoordinate $coordinate, ?float $speed = null, ?float $heading = null, ?float $altitude = null): DomainGPSHistory
    {
        return DB::transaction(function () use ($technicianId, $coordinate, $speed, $heading, $altitude) {
            $domainHistory = DomainGPSHistory::create(
                $technicianId,
                $coordinate,
                $speed,
                $heading,
                $altitude
            );

            GPSHistory::create([
                'uuid' => $domainHistory->id->value,
                'technician_id' => $technicianId->value,
                'latitude' => $coordinate->latitude,
                'longitude' => $coordinate->longitude,
                'speed' => $speed,
                'heading' => $heading,
                'altitude' => $altitude,
                'logged_at' => $domainHistory->loggedAt,
            ]);

            return $domainHistory;
        });
    }

    public function getTechnicianLastLocation(Uuid $technicianId): ?GPSHistory
    {
        return GPSHistory::where('technician_id', $technicianId->value)
            ->latest('logged_at')
            ->first();
    }

    public function getTechnicianDistance(Uuid $technicianId, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        $points = GPSHistory::where('technician_id', $technicianId->value)
            ->whereBetween('logged_at', [$start, $end])
            ->orderBy('logged_at')
            ->get();

        if ($points->count() < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;
        $prev = $points->first();

        foreach ($points->skip(1) as $point) {
            $totalDistance += $this->calculateDistance(
                $prev->latitude,
                $prev->longitude,
                $point->latitude,
                $point->longitude
            );
            $prev = $point;
        }

        return $totalDistance;
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
