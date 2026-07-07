<?php

namespace Src\Domain\Analytics\Services;

use Src\Domain\Analytics\CoverageAnalytics;
use Src\Domain\Analytics\Repositories\CoverageAnalyticsRepositoryInterface;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\ValueObjects\CoverageMetric;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageService
{
    public function __construct(
        private CoverageAnalyticsRepositoryInterface $coverageRepository
    ) {}

    public function calculateCoveragePercentage(
        float $coveredArea,
        float $totalArea
    ): float {
        if ($totalArea <= 0) {
            return 0.0;
        }
        return ($coveredArea / $totalArea) * 100;
    }

    public function calculateCustomerCoveragePercentage(
        int $coveredCustomers,
        int $totalCustomers
    ): float {
        if ($totalCustomers <= 0) {
            return 0.0;
        }
        return ($coveredCustomers / $totalCustomers) * 100;
    }

    public function getCoverageTrend(string $areaId, int $months = 6): array
    {
        $historical = $this->coverageRepository->findHistorical($areaId, $months);
        
        $trends = [];
        foreach ($historical as $analytics) {
            $trends[] = [
                'date' => $analytics->generatedAt->format('Y-m'),
                'coverage_percentage' => $analytics->metrics->coveragePercentage,
                'customer_coverage' => $analytics->metrics->customerCoveragePercentage,
                'covered_area' => $analytics->metrics->coveredArea,
                'covered_customers' => $analytics->metrics->coveredCustomers,
            ];
        }

        return $trends;
    }

    public function getCoverageGapAnalysis(string $areaId): array
    {
        $latest = $this->coverageRepository->findLatestByArea($areaId);
        
        if (!$latest) {
            return [
                'has_gaps' => false,
                'gaps' => [],
            ];
        }

        $gaps = [];
        
        if ($latest->metrics->coveragePercentage < 70) {
            $gaps[] = [
                'type' => 'area_coverage',
                'severity' => 'high',
                'message' => 'Area coverage below 70%',
                'current' => $latest->metrics->coveragePercentage,
                'target' => 90,
            ];
        }

        if ($latest->metrics->customerCoveragePercentage < 80) {
            $gaps[] = [
                'type' => 'customer_coverage',
                'severity' => 'high',
                'message' => 'Customer coverage below 80%',
                'current' => $latest->metrics->customerCoveragePercentage,
                'target' => 95,
            ];
        }

        return [
            'has_gaps' => !empty($gaps),
            'gaps' => $gaps,
        ];
    }

    public function getCoverageRecommendations(string $areaId): array
    {
        $gapAnalysis = $this->getCoverageGapAnalysis($areaId);
        $recommendations = [];

        foreach ($gapAnalysis['gaps'] as $gap) {
            $recommendations[] = match($gap['type']) {
                'area_coverage' => [
                    'action' => 'expand_coverage',
                    'priority' => $gap['severity'] === 'high' ? 'critical' : 'medium',
                    'description' => "Increase area coverage from {$gap['current']}% to {$gap['target']}%",
                    'suggested_locations' => $this->identifyUncoveredAreas($areaId),
                ],
                'customer_coverage' => [
                    'action' => 'improve_customer_access',
                    'priority' => $gap['severity'] === 'high' ? 'high' : 'medium',
                    'description' => "Improve service accessibility for {$gap['target'] - $gap['current']}% more customers",
                ],
                default => null,
            };
        }

        return array_filter($recommendations);
    }

    private function identifyUncoveredAreas(string $areaId): array
    {
        return [
            ['lat' => -6.2, 'lon' => 106.8, 'potential_customers' => 500],
            ['lat' => -6.25, 'lon' => 106.85, 'potential_customers' => 300],
        ];
    }

    public function generateCoverageReport(string $areaId): array
    {
        $latest = $this->coverageRepository->findLatestByArea($areaId);
        $historical = $this->coverageRepository->findHistorical($areaId, 12);
        
        $growthTrend = $this->calculateGrowthTrend($historical);
        $gapAnalysis = $this->getCoverageGapAnalysis($areaId);
        $recommendations = $this->getCoverageRecommendations($areaId);

        return [
            'area_id' => $areaId,
            'current' => $latest?->toArray(),
            'growth_trend' => $growthTrend,
            'gap_analysis' => $gapAnalysis,
            'recommendations' => $recommendations,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function calculateGrowthTrend(array $historical): array
    {
        if (count($historical) < 2) {
            return ['trend' => 'stable', 'rate' => 0];
        }

        $firstCoverage = $historical[0]->metrics->coveragePercentage;
        $lastCoverage = $historical[count($historical) - 1]->metrics->coveragePercentage;
        
        $growthRate = $firstCoverage > 0 
            ? (($lastCoverage - $firstCoverage) / $firstCoverage) * 100 
            : 0;

        return [
            'trend' => $growthRate > 5 ? 'improving' : ($growthRate < -5 ? 'declining' : 'stable'),
            'rate' => round($growthRate, 2),
            'period_months' => count($historical),
        ];
    }
}
