<?php

namespace App\Services\GIS;

use Illuminate\Support\Facades\DB;
use Src\Domain\GIS\Events\GeoRouteCreated;
use Src\Domain\GIS\GeoPoint;
use Src\Domain\GIS\GeoRoute;
use Src\Domain\GIS\Repositories\GeoPointRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoRouteRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GISService
{
    public function __construct(
        protected GeoPointRepositoryInterface $geoPointRepository,
        protected GeoRouteRepositoryInterface $geoRouteRepository,
        protected CoordinateService $coordinateService,
    ) {}

    public function createGeoPoint(
        Coordinate $coordinate,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null
    ): GeoPoint {
        $geoPoint = GeoPoint::create($coordinate, $name, $description, $metadata);
        $this->geoPointRepository->save($geoPoint);

        return $geoPoint;
    }

    public function createGeoRoute(
        array $points,
        ?GeoDistance $totalDistance = null,
        ?int $totalTimeSeconds = null,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null
    ): GeoRoute {
        $calculatedDistance = $totalDistance ?? $this->calculateRouteDistance($points);
        $geoRoute = GeoRoute::create($points, $calculatedDistance, $totalTimeSeconds, $name, $description, $metadata);

        $this->geoRouteRepository->save($geoRoute);
        event(GeoRouteCreated::create($geoRoute->id, $calculatedDistance->meters, $totalTimeSeconds));

        return $geoRoute;
    }

    private function calculateRouteDistance(array $points): GeoDistance
    {
        $totalDistance = 0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $distance = $this->coordinateService->calculateDistance($points[$i], $points[$i + 1]);
            $totalDistance += $distance->meters;
        }

        return GeoDistance::fromMeters($totalDistance);
    }

    public function toGeoJson($entity): array
    {
        return $entity->toGeoJson();
    }
}
