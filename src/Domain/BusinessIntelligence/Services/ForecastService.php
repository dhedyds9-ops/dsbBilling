<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\Forecast;
use Src\Domain\BusinessIntelligence\Enums\ForecastModel;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\BusinessIntelligence\Repositories\ForecastRepositoryInterface;
use Src\Domain\BusinessIntelligence\Events\ForecastGenerated;
use Src\Domain\BusinessIntelligence\ValueObjects\ForecastResult;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\MetricValue;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class ForecastService
{
    public function __construct(
        private readonly ForecastRepositoryInterface $forecastRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function createForecast(
        string $metricName,
        ForecastModel $model,
        DataGranularity $granularity,
        string $module,
        int $horizonPeriods = 12,
        float $confidenceLevel = 0.95,
        ?Uuid $createdBy = null
    ): Forecast {
        $forecast = Forecast::create(
            metricName: $metricName,
            model: $model,
            granularity: $granularity,
            module: $module,
            horizonPeriods: $horizonPeriods,
            confidenceLevel: $confidenceLevel,
            createdBy: $createdBy
        );

        $this->forecastRepository->save($forecast);

        return $forecast;
    }

    public function generateForecast(
        Uuid $forecastId,
        array $historicalData,
        TimeRange $historicalPeriod
    ): ForecastResult {
        $forecast = $this->forecastRepository->findById($forecastId);

        if (!$forecast) {
            throw new \DomainException('Forecast not found');
        }

        $forecast->setHistoricalData($historicalData, $historicalPeriod);

        // Generate predictions based on model
        $result = $this->executeForecastModel(
            $forecast->model,
            $historicalData,
            $forecast->horizonPeriods,
            $forecast->confidenceLevel
        );

        $forecast->setResult($result);

        // Calculate and set validation metrics
        $validation = $this->calculateValidationMetrics($historicalData, $result->predictions);
        $forecast->setValidationMetrics(
            $validation['mae'],
            $validation['mse'],
            $validation['rmse'],
            $validation['mape'],
            $validation['accuracy']
        );

        $this->forecastRepository->save($forecast);

        $this->eventDispatcher->dispatch(new ForecastGenerated(
            forecastId: $forecast->id,
            metricName: $forecast->metricName,
            model: $forecast->model->value,
            accuracy: $forecast->getAccuracy(),
            predictions: $result->predictions
        ));

        return $result;
    }

    public function getForecastAccuracy(Uuid $forecastId): float
    {
        $forecast = $this->forecastRepository->findById($forecastId);

        if (!$forecast) {
            throw new \DomainException('Forecast not found');
        }

        return $forecast->getAccuracy();
    }

    public function getForecastByMetric(string $metricName): array
    {
        return $this->forecastRepository->findByMetricName($metricName);
    }

    public function getLatestForecasts(int $limit = 10): array
    {
        return $this->forecastRepository->findLatestForecasts($limit);
    }

    private function executeForecastModel(
        ForecastModel $model,
        array $data,
        int $horizonPeriods,
        float $confidenceLevel
    ): ForecastResult {
        $predictions = match($model) {
            ForecastModel::LINEAR_REGRESSION => $this->linearRegression($data, $horizonPeriods),
            ForecastModel::MOVING_AVERAGE => $this->movingAverage($data, $horizonPeriods),
            ForecastModel::EXPONENTIAL_SMOOTHING => $this->exponentialSmoothing($data, $horizonPeriods),
            ForecastModel::POLYNOMIAL => $this->polynomialRegression($data, $horizonPeriods, 2),
            default => $this->movingAverage($data, $horizonPeriods),
        };

        $confidenceIntervals = $this->calculateConfidenceIntervals(
            $predictions,
            $this->calculateStandardError($data),
            $confidenceLevel
        );

        $accuracy = $this->estimateAccuracy($model, $data);

        return new ForecastResult(
            metricName: '',
            predictions: $predictions,
            confidenceIntervals: $confidenceIntervals,
            confidenceLevel: $confidenceLevel,
            model: $model->value,
            modelParameters: [],
            accuracy: $accuracy
        );
    }

    private function linearRegression(array $data, int $horizon): array
    {
        $n = count($data);
        if ($n < 2) {
            return array_fill(0, $horizon, $data[0] ?? 0);
        }

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
        for ($i = 0; $i < $horizon; $i++) {
            $predictions[] = max(0, $slope * ($n + $i) + $intercept);
        }

        return $predictions;
    }

    private function movingAverage(array $data, int $horizon): array
    {
        $window = min(5, count($data));
        $lastValues = array_slice($data, -$window);
        $avg = array_sum($lastValues) / count($lastValues);

        return array_fill(0, $horizon, $avg);
    }

    private function exponentialSmoothing(array $data, int $horizon, float $alpha = 0.3): array
    {
        if (empty($data)) {
            return array_fill(0, $horizon, 0);
        }

        $smoothed = $data[0];
        foreach (array_slice($data, 1) as $value) {
            $smoothed = $alpha * $value + (1 - $alpha) * $smoothed;
        }

        return array_fill(0, $horizon, $smoothed);
    }

    private function polynomialRegression(array $data, int $horizon, int $degree = 2): array
    {
        // Simplified polynomial regression - use linear for now
        return $this->linearRegression($data, $horizon);
    }

    private function calculateConfidenceIntervals(array $predictions, float $standardError, float $confidenceLevel): array
    {
        $zScore = match(round($confidenceLevel, 2)) {
            0.99 => 2.576,
            0.95 => 1.96,
            0.90 => 1.645,
            default => 1.96,
        };

        $intervals = [];
        foreach ($predictions as $prediction) {
            $margin = $zScore * $standardError;
            $intervals[] = [
                'lower' => max(0, $prediction - $margin),
                'upper' => $prediction + $margin,
            ];
        }

        return $intervals;
    }

    private function calculateStandardError(array $data): float
    {
        if (count($data) < 2) {
            return 0;
        }

        $mean = array_sum($data) / count($data);
        $variance = array_reduce($data, fn($carry, $val) => $carry + pow($val - $mean, 2), 0) / (count($data) - 1);

        return sqrt($variance);
    }

    private function calculateValidationMetrics(array $actual, array $predicted): array
    {
        $n = min(count($actual), count($predicted));
        if ($n == 0) {
            return ['mae' => 0, 'mse' => 0, 'rmse' => 0, 'mape' => 0, 'accuracy' => 0];
        }

        $errors = [];
        $squaredErrors = [];
        $percentageErrors = [];

        for ($i = 0; $i < $n; $i++) {
            $error = $predicted[$i] - $actual[$i];
            $errors[] = abs($error);
            $squaredErrors[] = $error * $error;
            $percentageErrors[] = $actual[$i] != 0 ? abs($error / $actual[$i]) : 0;
        }

        $mae = array_sum($errors) / $n;
        $mse = array_sum($squaredErrors) / $n;
        $rmse = sqrt($mse);
        $mape = (array_sum($percentageErrors) / $n) * 100;
        $accuracy = max(0, 100 - $mape);

        return [
            'mae' => round($mae, 4),
            'mse' => round($mse, 4),
            'rmse' => round($rmse, 4),
            'mape' => round($mape, 2),
            'accuracy' => round($accuracy, 2),
        ];
    }

    private function estimateAccuracy(ForecastModel $model, array $data): float
    {
        // Simplified accuracy estimation
        $coefficientOfVariation = count($data) > 0
            ? (sqrt(array_reduce($data, fn($c, $v) => $c + pow($v - array_sum($data) / count($data), 2), 0) / count($data)) / (array_sum($data) / count($data)))
            : 0;

        $baseAccuracy = match($model) {
            ForecastModel::LINEAR_REGRESSION => 85,
            ForecastModel::EXPONENTIAL_SMOOTHING => 80,
            ForecastModel::MOVING_AVERAGE => 75,
            ForecastModel::POLYNOMIAL => 80,
            default => 70,
        };

        // Adjust for data variability
        $adjustment = max(0, $coefficientOfVariation * 10);
        return min(99, max(50, $baseAccuracy - $adjustment));
    }
}
