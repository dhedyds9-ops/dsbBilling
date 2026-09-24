<?php

namespace Src\Domain\Analytics\Services;

use Src\Domain\Analytics\NetworkHeatmap;
use Src\Domain\Analytics\Repositories\NetworkHeatmapRepositoryInterface;
use Src\Domain\Analytics\Enums\HeatmapLayer;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\ValueObjects\GeoCoordinate;
use Src\Domain\Analytics\ValueObjects\HeatmapCell;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class HeatmapService
{
    public function __construct(
        private NetworkHeatmapRepositoryInterface $heatmapRepository
    ) {}

    public function generateHeatmap(
        string $areaId,
        string $areaName,
        HeatmapLayer $layer,
        GeoCoordinate $center,
        float $radiusKm,
        array $dataPoints,
        float $gridSizeKm = 0.5
    ): NetworkHeatmap {
        $heatmap = NetworkHeatmap::create(
            Uuid::generate(),
            $areaId,
            $areaName,
            $layer,
            $center,
            $radiusKm,
            $gridSizeKm
        );

        $cells = $this->createCellsFromDataPoints($heatmap, $dataPoints, $layer);
        $heatmap->setCells($cells);
        $heatmap->fireUpdateEvent();

        $this->heatmapRepository->save($heatmap);
        
        return $heatmap;
    }

    private function createCellsFromDataPoints(
        NetworkHeatmap $heatmap,
        array $dataPoints,
        HeatmapLayer $layer
    ): array {
        $cells = [];
        $latStep = ($heatmap->maxLat - $heatmap->minLat) / ceil(($heatmap->maxLat - $heatmap->minLat) / ($heatmap->gridSizeKm / 111));
        $lonStep = ($heatmap->maxLon - $heatmap->minLon) / ceil(($heatmap->maxLon - $heatmap->minLon) / ($heatmap->gridSizeKm / (111 * cos(deg2rad($heatmap->maxLat)))));

        $gridX = 0;
        for ($lat = $heatmap->minLat; $lat < $heatmap->maxLat; $lat += $latStep) {
            $gridY = 0;
            for ($lon = $heatmap->minLon; $lon < $heatmap->maxLon; $lon += $lonStep) {
                $cellLat = $lat + ($latStep / 2);
                $cellLon = $lon + ($lonStep / 2);
                
                $cellValue = $this->calculateCellValue(
                    $dataPoints,
                    $cellLat,
                    $cellLon,
                    $latStep * 111,
                    $lonStep * 111 * cos(deg2rad($cellLat)),
                    $layer
                );

                $customerCount = $this->countCustomersInCell($dataPoints, $cellLat, $cellLon, $latStep, $lonStep);
                $capacityUsage = $this->calculateCapacityUsage($dataPoints, $cellLat, $cellLon, $latStep, $lonStep);

                $cells[] = new HeatmapCell(
                    x: $gridX,
                    y: $gridY,
                    centerLat: $cellLat,
                    centerLon: $cellLon,
                    value: $cellValue,
                    intensity: min(1.0, $cellValue / 100),
                    color: $heatmap->getColorForValue($cellValue),
                    customerCount: $customerCount,
                    capacityUsage: $capacityUsage
                );

                $gridY++;
            }
            $gridX++;
        }

        return $cells;
    }

    private function calculateCellValue(
        array $dataPoints,
        float $centerLat,
        float $centerLon,
        float $latRadius,
        float $lonRadius,
        HeatmapLayer $layer
    ): float {
        $center = new GeoCoordinate($centerLat, $centerLon);
        $radius = max($latRadius, $lonRadius) / 2;

        $matchingPoints = array_filter(
            $dataPoints,
            fn($point) => (new GeoCoordinate($point['lat'], $point['lon']))->distanceTo($center) <= $radius
        );

        if (empty($matchingPoints)) {
            return 0.0;
        }

        return match($layer) {
            HeatmapLayer::COVERAGE => $this->calculateCoverageValue($matchingPoints),
            HeatmapLayer::CAPACITY => $this->calculateCapacityValue($matchingPoints),
            HeatmapLayer::CUSTOMERS => $this->calculateCustomerDensityValue($matchingPoints, $radius),
            HeatmapLayer::GROWTH => $this->calculateGrowthValue($matchingPoints),
            HeatmapLayer::CONGESTION => $this->calculateCongestionValue($matchingPoints),
        };
    }

    private function calculateCoverageValue(array $points): float
    {
        $covered = count(array_filter($points, fn($p) => ($p['covered'] ?? false)));
        return count($points) > 0 ? ($covered / count($points)) * 100 : 0;
    }

    private function calculateCapacityValue(array $points): float
    {
        $totalUsed = array_sum(array_column($points, 'used_capacity'));
        $totalCapacity = array_sum(array_column($points, 'total_capacity'));
        return $totalCapacity > 0 ? ($totalUsed / $totalCapacity) * 100 : 0;
    }

    private function calculateCustomerDensityValue(array $points, float $areaKm2): float
    {
        $customerCount = array_sum(array_column($points, 'customer_count'));
        return $areaKm2 > 0 ? ($customerCount / $areaKm2) * 100 : 0;
    }

    private function calculateGrowthValue(array $points): float
    {
        $growthRates = array_column($points, 'growth_rate');
        return count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0;
    }

    private function calculateCongestionValue(array $points): float
    {
        $congestions = array_column($points, 'congestion_level');
        return count($congestions) > 0 ? array_sum($congestions) / count($congestions) * 100 : 0;
    }

    private function countCustomersInCell(
        array $dataPoints,
        float $centerLat,
        float $centerLon,
        float $latStep,
        float $lonStep
    ): int {
        $center = new GeoCoordinate($centerLat, $centerLon);
        
        return count(array_filter(
            $dataPoints,
            fn($point) => (new GeoCoordinate($point['lat'], $point['lon']))->distanceTo($center) <= max($latStep, $lonStep) / 2
        ));
    }

    private function calculateCapacityUsage(
        array $dataPoints,
        float $centerLat,
        float $centerLon,
        float $latStep,
        float $lonStep
    ): float {
        $matchingPoints = array_filter(
            $dataPoints,
            fn($point) => (new GeoCoordinate($point['lat'], $point['lon']))->distanceTo(
                new GeoCoordinate($centerLat, $centerLon)
            ) <= max($latStep, $lonStep) / 2
        );

        return $this->calculateCapacityValue($matchingPoints);
    }

    public function updateHeatmap(string $heatmapId, array $newDataPoints): NetworkHeatmap
    {
        $heatmap = $this->heatmapRepository->findById(Uuid::fromString($heatmapId));
        
        if (!$heatmap) {
            throw new \InvalidArgumentException("Heatmap not found");
        }

        $cells = $this->createCellsFromDataPoints($heatmap, $newDataPoints, $heatmap->layer);
        $heatmap->setCells($cells);
        $heatmap->fireUpdateEvent();

        $this->heatmapRepository->save($heatmap);
        
        return $heatmap;
    }

    public function getHeatmapByAreaAndLayer(string $areaId, HeatmapLayer $layer): ?NetworkHeatmap
    {
        return $this->heatmapRepository->findLatestByAreaAndLayer($areaId, $layer);
    }

    public function getCombinedHeatmaps(string $areaId, array $layers): array
    {
        $combined = [];
        
        foreach ($layers as $layer) {
            $heatmap = $this->heatmapRepository->findLatestByAreaAndLayer(
                $areaId,
                $layer instanceof HeatmapLayer ? $layer : HeatmapLayer::from($layer)
            );
            
            if ($heatmap) {
                $combined[] = $heatmap;
            }
        }

        return $combined;
    }
}
