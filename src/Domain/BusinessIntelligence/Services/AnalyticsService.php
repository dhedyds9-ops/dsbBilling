<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\Analytics;
use Src\Domain\BusinessIntelligence\Repositories\AnalyticsRepositoryInterface;
use Src\Domain\BusinessIntelligence\Events\AnalyticsComputed;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\AggregationRule;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class AnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepositoryInterface $analyticsRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function createAnalytics(
        string $name,
        string $type,
        string $module,
        ?Uuid $createdBy = null
    ): Analytics {
        $analytics = Analytics::create(
            name: $name,
            type: $type,
            module: $module,
            createdBy: $createdBy
        );

        $this->analyticsRepository->save($analytics);

        return $analytics;
    }

    public function configureAnalytics(
        Uuid $analyticsId,
        TimeRange $timeRange,
        array $metrics,
        array $dimensions,
        ?array $segments = null,
        ?array $comparisons = null
    ): Analytics {
        $analytics = $this->analyticsRepository->findById($analyticsId);

        if (!$analytics) {
            throw new \DomainException('Analytics not found');
        }

        $analytics->setTimeRange($timeRange);

        foreach ($metrics as $metric) {
            if ($metric instanceof AggregationRule) {
                $analytics->addMetric($metric->getAlias(), $metric);
            }
        }

        foreach ($dimensions as $dimension) {
            $analytics->addDimension($dimension);
        }

        if ($segments) {
            foreach ($segments as $segment) {
                $analytics->addSegment($segment['name'], $segment['conditions']);
            }
        }

        if ($comparisons) {
            foreach ($comparisons as $comparison) {
                $comparisonTimeRange = TimeRange::create(
                    startDate: new \DateTimeImmutable($comparison['start_date']),
                    endDate: new \DateTimeImmutable($comparison['end_date'])
                );
                $analytics->addComparison(
                    $comparison['name'],
                    $comparisonTimeRange,
                    $comparison['label'] ?? null
                );
            }
        }

        $this->analyticsRepository->save($analytics);

        return $analytics;
    }

    public function computeAnalytics(Uuid $analyticsId): array
    {
        $analytics = $this->analyticsRepository->findById($analyticsId);

        if (!$analytics) {
            throw new \DomainException('Analytics not found');
        }

        // Compute analytics based on configuration
        $results = $this->executeComputation($analytics);

        $this->eventDispatcher->dispatch(new AnalyticsComputed(
            analyticsId: $analytics->id,
            analyticsName: $analytics->name,
            type: $analytics->type,
            metrics: $results
        ));

        return $results;
    }

    public function getTrendAnalysis(
        string $metricName,
        TimeRange $period,
        string $granularity = 'daily'
    ): array {
        // Calculate trend based on historical data
        $data = $this->getHistoricalData($metricName, $period);

        if (count($data) < 2) {
            return [
                'trend' => 'stable',
                'change_percentage' => 0,
                'change_absolute' => 0,
            ];
        }

        $firstHalf = array_slice($data, 0, (int)(count($data) / 2));
        $secondHalf = array_slice($data, (int)(count($data) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $changeAbsolute = $secondAvg - $firstAvg;
        $changePercentage = $firstAvg != 0 ? ($changeAbsolute / $firstAvg) * 100 : 0;

        $trend = abs($changePercentage) < 5 ? 'stable' : ($changePercentage > 0 ? 'increasing' : 'decreasing');

        return [
            'trend' => $trend,
            'change_percentage' => round($changePercentage, 2),
            'change_absolute' => round($changeAbsolute, 2),
            'data_points' => count($data),
            'min' => min($data),
            'max' => max($data),
            'average' => array_sum($data) / count($data),
        ];
    }

    public function getGrowthPrediction(
        string $metricName,
        TimeRange $historicalPeriod,
        int $futurePeriods = 12
    ): array {
        // Simple linear regression for growth prediction
        $data = $this->getHistoricalData($metricName, $historicalPeriod);

        if (count($data) < 3) {
            return [
                'predictions' => [],
                'growth_rate' => 0,
                'confidence' => 'low',
            ];
        }

        $n = count($data);
        $xSum = 0;
        $ySum = 0;
        $xySum = 0;
        $xxSum = 0;

        for ($i = 0; $i < $n; $i++) {
            $xSum += $i;
            $ySum += $data[$i];
            $xySum += $i * $data[$i];
            $xxSum += $i * $i;
        }

        $slope = ($n * $xySum - $xSum * $ySum) / ($n * $xxSum - $xSum * $xSum);
        $intercept = ($ySum - $slope * $xSum) / $n;

        $predictions = [];
        for ($i = 0; $i < $futurePeriods; $i++) {
            $predictions[] = $slope * ($n + $i) + $intercept;
        }

        $mean = array_sum($data) / $n;
        $variance = array_reduce($data, fn($carry, $val) => $carry + pow($val - $mean, 2), 0) / $n;
        $stdDev = sqrt($variance);
        $confidence = $stdDev / $mean < 0.2 ? 'high' : ($stdDev / $mean < 0.5 ? 'medium' : 'low');

        return [
            'predictions' => $predictions,
            'slope' => $slope,
            'intercept' => $intercept,
            'growth_rate' => $slope / $mean * 100,
            'confidence' => $confidence,
            'standard_deviation' => $stdDev,
        ];
    }

    public function getComparativeAnalysis(
        string $metricName,
        TimeRange $currentPeriod,
        TimeRange $comparisonPeriod
    ): array {
        $currentData = $this->aggregateMetricData($metricName, $currentPeriod);
        $comparisonData = $this->aggregateMetricData($metricName, $comparisonPeriod);

        $currentSum = array_sum($currentData);
        $comparisonSum = array_sum($comparisonData);

        $changeAbsolute = $currentSum - $comparisonSum;
        $changePercentage = $comparisonSum != 0 ? ($changeAbsolute / $comparisonSum) * 100 : 0;

        return [
            'current_period' => $currentPeriod->toArray(),
            'comparison_period' => $comparisonPeriod->toArray(),
            'current_value' => $currentSum,
            'comparison_value' => $comparisonSum,
            'change_absolute' => $changeAbsolute,
            'change_percentage' => round($changePercentage, 2),
        ];
    }

    private function executeComputation(Analytics $analytics): array
    {
        // Placeholder - in real implementation this would execute the analytics query
        return [
            'dimensions' => $analytics->getDimensions(),
            'metrics' => $analytics->getMetrics(),
            'data' => [],
        ];
    }

    private function getHistoricalData(string $metricName, TimeRange $period): array
    {
        // Placeholder - in real implementation this would fetch historical data
        return [];
    }

    private function aggregateMetricData(string $metricName, TimeRange $period): array
    {
        // Placeholder - in real implementation this would aggregate data
        return [];
    }
}
