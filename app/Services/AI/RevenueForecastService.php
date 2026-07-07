<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RevenueForecastService
{
    private const FORECAST_PERIODS = [3, 6, 12];

    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function forecastRevenue(
        Uuid $forecastId,
        array $historicalData,
        int $periods = 12,
        ?string $modelId = null
    ): array {
        Log::info("RevenueForecast: Generating forecast {$forecastId} for {$periods} periods");

        $validatedData = $this->validateHistoricalData($historicalData);
        $trend = $this->calculateTrend($validatedData);
        $seasonality = $this->detectSeasonality($validatedData);

        $mlForecast = $this->aiConnector->analyze('revenue_forecast', [
            'historical' => $validatedData,
            'periods' => $periods,
        ]);

        $predictions = $this->generatePredictions($validatedData, $periods, $trend, $seasonality);
        $predictedTotal = array_sum(array_column($predictions, 'revenue'));
        $growthRate = $this->calculateGrowthRate($validatedData, $predictions);

        return [
            'forecast_id' => $forecastId->toString(),
            'periods' => $periods,
            'predictions' => $predictions,
            'predicted_total' => round($predictedTotal, 2),
            'growth_rate' => round($growthRate, 4),
            'trend' => $trend,
            'seasonality_detected' => $seasonality !== null,
            'confidence' => $this->calculateConfidence($validatedData, $predictions),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function batchForecast(array $forecasts): array
    {
        $results = [];

        foreach ($forecasts as $forecast) {
            $results[$forecast['id']] = $this->forecastRevenue(
                Uuid::fromString($forecast['id']),
                $forecast['historical_data'],
                $forecast['periods'] ?? 12
            );
        }

        return $results;
    }

    private function validateHistoricalData(array $data): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Historical data cannot be empty');
        }

        return array_map(function ($item) {
            return [
                'date' => $item['date'] ?? null,
                'revenue' => floatval($item['revenue'] ?? 0),
                'subscriptions' => intval($item['subscriptions'] ?? 0),
                'churned' => intval($item['churned'] ?? 0),
                'new_customers' => intval($item['new_customers'] ?? 0),
            ];
        }, $data);
    }

    private function calculateTrend(array $data): string
    {
        if (count($data) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($data, 0, (int)(count($data) / 2));
        $secondHalf = array_slice($data, (int)(count($data) / 2));

        $firstAvg = array_sum(array_column($firstHalf, 'revenue')) / count($firstHalf);
        $secondAvg = array_sum(array_column($secondHalf, 'revenue')) / count($secondHalf);

        $changePercent = (($secondAvg - $firstAvg) / $firstAvg) * 100;

        return match (true) {
            $changePercent > 10 => 'increasing',
            $changePercent < -10 => 'decreasing',
            default => 'stable',
        };
    }

    private function detectSeasonality(array $data): ?array
    {
        if (count($data) < 12) {
            return null;
        }

        $monthlyAvg = [];
        foreach ($data as $item) {
            $month = date('n', strtotime($item['date']));
            $monthlyAvg[$month][] = $item['revenue'];
        }

        $seasonalIndices = [];
        $overallAvg = array_sum(array_column($data, 'revenue')) / count($data);

        foreach ($monthlyAvg as $month => $values) {
            $monthAvg = array_sum($values) / count($values);
            $seasonalIndices[$month] = $overallAvg > 0 ? $monthAvg / $overallAvg : 1;
        }

        $variance = $this->calculateVariance(array_values($seasonalIndices));

        return $variance > 0.1 ? $seasonalIndices : null;
    }

    private function generatePredictions(
        array $historicalData,
        int $periods,
        string $trend,
        ?array $seasonality
    ): array {
        $predictions = [];
        $lastValue = end($historicalData)['revenue'];
        $trendFactor = match ($trend) {
            'increasing' => 1.05,
            'decreasing' => 0.95,
            default => 1.0,
        };

        $baseDate = !empty($historicalData)
            ? strtotime(end($historicalData)['date'])
            : time();

        for ($i = 1; $i <= $periods; $i++) {
            $predictedRevenue = $lastValue * pow($trendFactor, $i);

            if ($seasonality) {
                $month = ($i % 12) + 1;
                $predictedRevenue *= $seasonality[$month] ?? 1;
            }

            $predictions[] = [
                'period' => $i,
                'date' => date('Y-m-d', strtotime("+{$i} month", $baseDate)),
                'revenue' => round($predictedRevenue, 2),
            ];
        }

        return $predictions;
    }

    private function calculateGrowthRate(array $historical, array $predictions): float
    {
        if (empty($historical) || empty($predictions)) {
            return 0.0;
        }

        $historicalAvg = array_sum(array_column($historical, 'revenue')) / count($historical);
        $predictionAvg = array_sum(array_column($predictions, 'revenue')) / count($predictions);

        return $historicalAvg > 0 ? (($predictionAvg - $historicalAvg) / $historicalAvg) : 0;
    }

    private function calculateConfidence(array $historical, array $predictions): float
    {
        $baseConfidence = 0.7;

        if (count($historical) >= 12) {
            $baseConfidence += 0.15;
        } elseif (count($historical) >= 6) {
            $baseConfidence += 0.1;
        }

        $variance = $this->calculateVariance(array_column($historical, 'revenue'));
        if ($variance < 0.1) {
            $baseConfidence += 0.1;
        }

        return min(0.95, $baseConfidence);
    }

    private function calculateVariance(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn($v) => pow($v - $mean, 2), $values);

        return array_sum($squaredDiffs) / count($values);
    }
}
