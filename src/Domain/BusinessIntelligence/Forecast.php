<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\Enums\ForecastModel;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\BusinessIntelligence\ValueObjects\ForecastResult;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\MetricValue;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Forecast extends AggregateRoot
{
    private array $historicalData = [];
    private ?ForecastResult $result = null;
    private array $modelParameters = [];
    private array $validationMetrics = [];
    private ?TimeRange $predictionPeriod = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly string $metricName,
        public readonly ForecastModel $model,
        public readonly DataGranularity $granularity,
        public readonly string $module,
        public readonly int $horizonPeriods,
        public readonly float $confidenceLevel = 0.95,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $metricName,
        ForecastModel $model,
        DataGranularity $granularity,
        string $module,
        int $horizonPeriods = 12,
        float $confidenceLevel = 0.95,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            metricName: $metricName,
            model: $model,
            granularity: $granularity,
            module: $module,
            horizonPeriods: $horizonPeriods,
            confidenceLevel: $confidenceLevel,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function setHistoricalData(array $data, TimeRange $timeRange): void
    {
        $this->historicalData = [
            'data' => $data,
            'time_range' => $timeRange->toArray(),
            'data_points' => count($data),
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setModelParameters(array $parameters): void
    {
        $this->modelParameters = $parameters;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setPredictionPeriod(TimeRange $period): void
    {
        $this->predictionPeriod = $period;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setResult(ForecastResult $result): void
    {
        $this->result = $result;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setValidationMetrics(
        float $mae,  // Mean Absolute Error
        float $mse,  // Mean Squared Error
        float $rmse, // Root Mean Squared Error
        float $mape, // Mean Absolute Percentage Error
        float $accuracy
    ): void {
        $this->validationMetrics = [
            'mae' => $mae,
            'mse' => $mse,
            'rmse' => $rmse,
            'mape' => $mape,
            'accuracy' => $accuracy,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getHistoricalData(): array
    {
        return $this->historicalData;
    }

    public function getResult(): ?ForecastResult
    {
        return $this->result;
    }

    public function getModelParameters(): array
    {
        return $this->modelParameters;
    }

    public function getValidationMetrics(): array
    {
        return $this->validationMetrics;
    }

    public function getPredictionPeriod(): ?TimeRange
    {
        return $this->predictionPeriod;
    }

    public function getAccuracy(): float
    {
        return $this->validationMetrics['accuracy'] ?? 0;
    }

    public function getModelLabel(): string
    {
        return $this->model->getLabel();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'metric_name' => $this->metricName,
            'model' => $this->model->value,
            'model_label' => $this->model->getLabel(),
            'granularity' => $this->granularity->value,
            'module' => $this->module,
            'horizon_periods' => $this->horizonPeriods,
            'confidence_level' => $this->confidenceLevel,
            'historical_data' => $this->historicalData,
            'result' => $this->result?->toArray(),
            'model_parameters' => $this->modelParameters,
            'validation_metrics' => $this->validationMetrics,
            'prediction_period' => $this->predictionPeriod?->toArray(),
            'accuracy' => $this->getAccuracy(),
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
