<?php

namespace Src\Domain\Analytics\Services;

use Src\Domain\Analytics\Enums\ExpansionPriority;
use Src\Domain\Analytics\Repositories\CustomerDistributionRepositoryInterface;
use Src\Domain\Analytics\Repositories\FiberAnalyticsRepositoryInterface;
use Src\Domain\Analytics\Repositories\CoverageAnalyticsRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ForecastService
{
    public function __construct(
        private CustomerDistributionRepositoryInterface $customerRepository,
        private FiberAnalyticsRepositoryInterface $fiberRepository,
        private CoverageAnalyticsRepositoryInterface $coverageRepository
    ) {}

    public function forecastGrowth(
        string $areaId,
        int $daysAhead = 90
    ): array {
        $historicalData = $this->getHistoricalGrowthData($areaId);
        
        if (empty($historicalData)) {
            return $this->getDefaultForecast($daysAhead);
        }

        $growthRate = $this->calculateGrowthRate($historicalData);
        $predictions = $this->generatePredictions($historicalData, $daysAhead, $growthRate);
        $confidence = $this->calculateConfidence($historicalData);

        return [
            'area_id' => $areaId,
            'forecast_days' => $daysAhead,
            'growth_rate' => $growthRate,
            'predictions' => $predictions,
            'confidence' => $confidence,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function forecastCapacity(
        string $areaId,
        int $daysAhead = 90
    ): array {
        $fiberAnalytics = $this->fiberRepository->findLatestByArea($areaId);
        
        if (!$fiberAnalytics) {
            return $this->getDefaultCapacityForecast($daysAhead);
        }

        $currentUtilization = $fiberAnalytics->utilizationPercentage;
        $dailyGrowthRate = $this->estimateDailyGrowthRate($areaId);
        $projections = [];

        for ($day = 1; $day <= $daysAhead; $day++) {
            $projectedUtilization = $currentUtilization + ($dailyGrowthRate * $day);
            $daysToCritical = $this->calculateDaysToThreshold($currentUtilization, $dailyGrowthRate, 90);
            $daysToWarning = $this->calculateDaysToThreshold($currentUtilization, $dailyGrowthRate, 75);

            $projections[] = [
                'day' => $day,
                'utilization' => round(min(100, $projectedUtilization), 2),
                'will_exceed_warning' => $day >= $daysToWarning,
                'will_exceed_critical' => $day >= $daysToCritical,
            ];
        }

        return [
            'area_id' => $areaId,
            'current_utilization' => $currentUtilization,
            'daily_growth_rate' => $dailyGrowthRate,
            'days_to_warning' => $daysToWarning,
            'days_to_critical' => $daysToCritical,
            'projections' => $projections,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function generateExpansionRecommendations(string $areaId): array
    {
        $capacityForecast = $this->forecastCapacity($areaId, 180);
        $growthForecast = $this->forecastGrowth($areaId, 180);
        
        $recommendations = [];

        if (($capacityForecast['days_to_critical'] ?? PHP_INT_MAX) <= 90) {
            $recommendations[] = [
                'priority' => ExpansionPriority::CRITICAL,
                'type' => 'capacity_expansion',
                'description' => 'Capacity will reach critical level within 90 days',
                'suggested_action' => 'Plan new OLT deployment or fiber expansion',
                'estimated_investment' => 'high',
                'timeline' => 'immediate',
            ];
        }

        if (($growthForecast['growth_rate'] ?? 0) > 10) {
            $recommendations[] = [
                'priority' => ExpansionPriority::HIGH,
                'type' => 'growth_preparation',
                'description' => 'High growth rate detected - prepare infrastructure',
                'suggested_action' => 'Increase inventory and technician capacity',
                'estimated_investment' => 'medium',
                'timeline' => '30_days',
            ];
        }

        $coldspots = $this->customerRepository->findColdspots();
        if (count($coldspots) > 0) {
            $recommendations[] = [
                'priority' => ExpansionPriority::MEDIUM,
                'type' => 'coverage_expansion',
                'description' => 'Coldspots identified - expand coverage to underserved areas',
                'suggested_action' => 'Deploy new ODPs in low-density areas',
                'estimated_investment' => 'medium',
                'timeline' => '90_days',
            ];
        }

        return [
            'area_id' => $areaId,
            'recommendations' => $recommendations,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function getHistoricalGrowthData(string $areaId): array
    {
        $historical = $this->customerRepository->findByAreaId($areaId);
        
        return array_map(
            fn($d) => [
                'date' => $d->generatedAt->format('Y-m-d'),
                'customer_count' => $d->totalCustomers,
                'growth' => $d->getNetGrowth(),
            ],
            $historical
        );
    }

    private function calculateGrowthRate(array $historical): float
    {
        if (count($historical) < 2) {
            return 0.0;
        }

        $firstCount = $historical[0]['customer_count'];
        $lastCount = $historical[count($historical) - 1]['customer_count'];
        
        if ($firstCount === 0) {
            return 0.0;
        }

        $totalGrowth = $lastCount - $firstCount;
        $daysBetween = count($historical);
        
        return ($totalGrowth / $firstCount) / $daysBetween * 30;
    }

    private function estimateDailyGrowthRate(string $areaId): float
    {
        $historical = $this->getHistoricalGrowthData($areaId);
        
        if (empty($historical)) {
            return 0.5;
        }

        $totalGrowth = 0;
        for ($i = 1; $i < count($historical); $i++) {
            $totalGrowth += $historical[$i]['growth'];
        }

        $days = count($historical);
        return $days > 0 ? $totalGrowth / $days : 0.5;
    }

    private function generatePredictions(array $historical, int $daysAhead, float $monthlyGrowthRate): array
    {
        $predictions = [];
        $lastCount = end($historical)['customer_count'] ?? 0;

        for ($day = 1; $day <= $daysAhead; $day++) {
            $growthSinceLast = ($monthlyGrowthRate / 30) * $day;
            $predictedCount = $lastCount * (1 + ($growthSinceLast / 100));

            $predictions[] = [
                'day' => $day,
                'predicted_customers' => (int) max(0, $predictedCount),
                'confidence_interval' => [
                    'low' => (int) max(0, $predictedCount * 0.9),
                    'high' => (int) ($predictedCount * 1.1),
                ],
            ];
        }

        return $predictions;
    }

    private function calculateConfidence(array $historical): array
    {
        $count = count($historical);
        
        if ($count < 3) {
            return ['level' => 'low', 'score' => 0.5];
        } elseif ($count < 6) {
            return ['level' => 'medium', 'score' => 0.7];
        }
        
        return ['level' => 'high', 'score' => 0.9];
    }

    private function calculateDaysToThreshold(float $current, float $dailyGrowth, float $threshold): int
    {
        if ($dailyGrowth <= 0) {
            return PHP_INT_MAX;
        }

        $remaining = $threshold - $current;
        
        if ($remaining <= 0) {
            return 0;
        }

        return (int) ceil($remaining / $dailyGrowth);
    }

    private function getDefaultForecast(int $daysAhead): array
    {
        return [
            'area_id' => 'unknown',
            'forecast_days' => $daysAhead,
            'growth_rate' => 0,
            'predictions' => [],
            'confidence' => ['level' => 'low', 'score' => 0.3],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function getDefaultCapacityForecast(int $daysAhead): array
    {
        return [
            'area_id' => 'unknown',
            'current_utilization' => 0,
            'daily_growth_rate' => 0,
            'days_to_warning' => PHP_INT_MAX,
            'days_to_critical' => PHP_INT_MAX,
            'projections' => [],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
