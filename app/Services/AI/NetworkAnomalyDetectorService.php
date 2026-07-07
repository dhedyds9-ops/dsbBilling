<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class NetworkAnomalyDetectorService
{
    private const THRESHOLD_STD_DEV = 2.5;
    private const ZSCORE_THRESHOLD = 3.0;

    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function detectAnomaly(
        string $deviceId,
        array $currentMetrics,
        array $historicalMetrics = []
    ): array {
        Log::info("NetworkAnomalyDetector: Analyzing metrics for device {$deviceId}");

        $zscoreResults = $this->detectZScoreAnomalies($currentMetrics, $historicalMetrics);
        $statisticalResults = $this->detectStatisticalAnomalies($currentMetrics, $historicalMetrics);

        $combinedResults = array_merge($zscoreResults, $statisticalResults);

        $isAnomaly = count($combinedResults) > 0;
        $severity = $this->determineSeverity($combinedResults);

        if ($isAnomaly && $severity === 'CRITICAL') {
            $mlAnalysis = $this->aiConnector->analyze("network_anomaly", [
                'device_id' => $deviceId,
                'metrics' => $currentMetrics,
                'anomalies' => $combinedResults,
            ]);
        }

        return [
            'device_id' => $deviceId,
            'is_anomaly' => $isAnomaly,
            'severity' => $severity,
            'anomaly_score' => $this->calculateAnomalyScore($combinedResults),
            'detected_issues' => $combinedResults,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function batchDetectNetworkAnomalies(array $deviceMetrics, ?string $modelId = null): array
    {
        $results = [];

        foreach ($deviceMetrics as $deviceId => $metrics) {
            $results[$deviceId] = $this->detectAnomaly($deviceId, $metrics);
        }

        return $results;
    }

    private function detectZScoreAnomalies(array $current, array $historical): array
    {
        $anomalies = [];

        if (count($historical) < 5) {
            return $this->detectStatisticalAnomalies($current, []);
        }

        foreach ($current as $metricName => $value) {
            $historicalValues = array_column($historical, $metricName);

            if (count($historicalValues) < 3) {
                continue;
            }

            $mean = array_sum($historicalValues) / count($historicalValues);
            $stdDev = $this->calculateStdDev($historicalValues, $mean);

            if ($stdDev == 0) {
                continue;
            }

            $zScore = abs(($value - $mean) / $stdDev);

            if ($zScore > self::ZSCORE_THRESHOLD) {
                $anomalies[] = [
                    'metric' => $metricName,
                    'value' => $value,
                    'expected_range' => [$mean - 2 * $stdDev, $mean + 2 * $stdDev],
                    'z_score' => $zScore,
                    'type' => 'zscore',
                ];
            }
        }

        return $anomalies;
    }

    private function detectStatisticalAnomalies(array $current, array $historical): array
    {
        $anomalies = [];

        foreach ($current as $metricName => $value) {
            $expectedRange = $this->getExpectedRange($metricName);

            if ($expectedRange && ($value < $expectedRange['min'] || $value > $expectedRange['max'])) {
                $anomalies[] = [
                    'metric' => $metricName,
                    'value' => $value,
                    'expected_range' => [$expectedRange['min'], $expectedRange['max']],
                    'deviation' => $this->calculateDeviation($value, $expectedRange),
                    'type' => 'statistical',
                ];
            }
        }

        return $anomalies;
    }

    private function getExpectedRange(string $metricName): ?array
    {
        $ranges = [
            'cpu_usage' => ['min' => 0, 'max' => 90],
            'memory_usage' => ['min' => 0, 'max' => 85],
            'temperature' => ['min' => 15, 'max' => 75],
            'latency' => ['min' => 0, 'max' => 100],
            'packet_loss' => ['min' => 0, 'max' => 5],
            'bandwidth_utilization' => ['min' => 0, 'max' => 95],
        ];

        return $ranges[$metricName] ?? null;
    }

    private function calculateStdDev(array $values, float $mean): float
    {
        $squaredDiffs = array_map(fn($v) => pow($v - $mean, 2), $values);
        $variance = array_sum($squaredDiffs) / count($values);
        return sqrt($variance);
    }

    private function calculateDeviation(float $value, array $range): float
    {
        $mid = ($range['min'] + $range['max']) / 2;
        $rangeSize = $range['max'] - $range['min'];

        if ($rangeSize == 0) {
            return 0;
        }

        return abs(($value - $mid) / ($rangeSize / 2)) * 100;
    }

    private function calculateAnomalyScore(array $anomalies): float
    {
        if (empty($anomalies)) {
            return 0.0;
        }

        $totalDeviation = array_sum(array_column($anomalies, 'deviation' ?? 1));
        return min(1.0, $totalDeviation / (count($anomalies) * 100));
    }

    private function determineSeverity(array $anomalies): string
    {
        if (empty($anomalies)) {
            return 'NORMAL';
        }

        $maxDeviation = max(array_column($anomalies, 'deviation' ?? [0]));

        return match (true) {
            $maxDeviation > 80 => 'CRITICAL',
            $maxDeviation > 50 => 'HIGH',
            $maxDeviation > 25 => 'MEDIUM',
            default => 'LOW',
        };
    }
}
