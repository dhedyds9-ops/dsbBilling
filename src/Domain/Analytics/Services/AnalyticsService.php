<?php

namespace Src\Domain\Analytics\Services;

use Src\Domain\Analytics\CoverageAnalytics;
use Src\Domain\Analytics\FiberAnalytics;
use Src\Domain\Analytics\CapacityAnalytics;
use Src\Domain\Analytics\CustomerDistribution;
use Src\Domain\Analytics\Repositories\CoverageAnalyticsRepositoryInterface;
use Src\Domain\Analytics\Repositories\FiberAnalyticsRepositoryInterface;
use Src\Domain\Analytics\Repositories\CapacityAnalyticsRepositoryInterface;
use Src\Domain\Analytics\Repositories\CustomerDistributionRepositoryInterface;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\Events\AnalyticsGenerated;
use Src\Domain\Analytics\ValueObjects\CoverageMetric;
use Src\Domain\Analytics\ValueObjects\DensityMetric;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AnalyticsService
{
    public function __construct(
        private CoverageAnalyticsRepositoryInterface $coverageRepository,
        private FiberAnalyticsRepositoryInterface $fiberRepository,
        private CapacityAnalyticsRepositoryInterface $capacityRepository,
        private CustomerDistributionRepositoryInterface $customerRepository
    ) {}

    public function generateCoverageAnalytics(
        string $areaId,
        string $areaName,
        float $coveredArea,
        float $totalArea,
        int $coveragePoints,
        int $coveredCustomers,
        int $totalCustomers,
        TimeRange $timeRange = TimeRange::MONTH
    ): CoverageAnalytics {
        $metrics = CoverageMetric::calculate(
            $coveredArea,
            $totalArea,
            $coveragePoints,
            $coveredCustomers,
            $totalCustomers
        );

        $previous = $this->coverageRepository->findLatestByArea($areaId);
        
        $analytics = CoverageAnalytics::create(
            Uuid::generate(),
            $areaId,
            $areaName,
            $metrics,
            AnalyticsType::COVERAGE,
            $timeRange
        );

        if ($previous) {
            $analytics->compareWithPrevious($previous);
        }

        $this->coverageRepository->save($analytics);
        
        return $analytics;
    }

    public function generateFiberAnalytics(
        string $areaId,
        string $areaName,
        int $totalCores,
        int $usedCores,
        int $reservedCores = 0
    ): FiberAnalytics {
        $analytics = FiberAnalytics::create(
            Uuid::generate(),
            $areaId,
            $areaName,
            $totalCores,
            $usedCores,
            $reservedCores
        );

        $this->fiberRepository->save($analytics);
        
        return $analytics;
    }

    public function generateCapacityAnalytics(
        string $areaId,
        string $areaName,
        int $totalOdp,
        int $activeOdp,
        int $totalOlt,
        int $activeOlt,
        int $totalPonPorts,
        int $usedPonPorts,
        int $totalOnuCapacity,
        int $usedOnuCapacity
    ): CapacityAnalytics {
        $analytics = CapacityAnalytics::create(
            Uuid::generate(),
            $areaId,
            $areaName
        );

        $analytics->totalOdp = $totalOdp;
        $analytics->activeOdp = $activeOdp;
        $analytics->totalOlt = $totalOlt;
        $analytics->activeOlt = $activeOlt;
        $analytics->totalPonPorts = $totalPonPorts;
        $analytics->usedPonPorts = $usedPonPorts;
        $analytics->totalOnuCapacity = $totalOnuCapacity;
        $analytics->usedOnuCapacity = $usedOnuCapacity;
        $analytics->calculateUtilization();

        $this->capacityRepository->save($analytics);
        
        return $analytics;
    }

    public function generateCustomerDistribution(
        string $areaId,
        string $areaName,
        int $totalCustomers,
        int $activeCustomers,
        int $totalCustomersInArea,
        float $areaKm2,
        int $totalCapacity,
        int $usedCapacity
    ): CustomerDistribution {
        $densityMetric = DensityMetric::calculate(
            $totalCustomers,
            $areaKm2,
            $totalCapacity,
            $usedCapacity
        );

        $distribution = CustomerDistribution::create(
            Uuid::generate(),
            $areaId,
            $areaName,
            $densityMetric,
            $totalCustomers,
            $activeCustomers
        );

        $this->customerRepository->save($distribution);
        
        return $distribution;
    }

    public function getAreaSummary(string $areaId): array
    {
        $coverage = $this->coverageRepository->findLatestByArea($areaId);
        $fiber = $this->fiberRepository->findLatestByArea($areaId);
        $capacity = $this->capacityRepository->findLatestByArea($areaId);
        $customer = $this->customerRepository->findLatestByArea($areaId);

        return [
            'area_id' => $areaId,
            'coverage' => $coverage?->toArray(),
            'fiber' => $fiber?->toArray(),
            'capacity' => $capacity?->toArray(),
            'customer_distribution' => $customer?->toArray(),
        ];
    }

    public function getNetworkOverview(): array
    {
        $criticalFiber = $this->fiberRepository->findCriticalUtilization(80);
        $criticalCapacity = $this->capacityRepository->findCriticalResources();
        $hotspots = $this->customerRepository->findHotspots();

        return [
            'critical_fiber_areas' => count($criticalFiber),
            'critical_capacity_resources' => count($criticalCapacity),
            'customer_hotspots' => count($hotspots),
            'health_score' => $this->calculateOverallHealthScore(),
        ];
    }

    private function calculateOverallHealthScore(): float
    {
        return 85.5;
    }
}
