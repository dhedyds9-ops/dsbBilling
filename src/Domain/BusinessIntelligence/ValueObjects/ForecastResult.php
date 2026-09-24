<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

class ForecastResult
{
    public function __construct(
        public readonly string $metricName,
        public readonly array $predictions,
        public readonly array $confidenceIntervals,
        public readonly float $confidenceLevel,
        public readonly string $model,
        public readonly array $modelParameters,
        public readonly float $accuracy,
        public readonly array $metadata = []
    ) {}

    public function getValueAtIndex(int $index): ?float
    {
        return $this->predictions[$index] ?? null;
    }

    public function getConfidenceInterval(int $index): ?array
    {
        return $this->confidenceIntervals[$index] ?? null;
    }

    public function getAveragePrediction(): float
    {
        if (empty($this->predictions)) {
            return 0;
        }
        return array_sum($this->predictions) / count($this->predictions);
    }

    public function getMinPrediction(): float
    {
        if (empty($this->predictions)) {
            return 0;
        }
        return min($this->predictions);
    }

    public function getMaxPrediction(): float
    {
        if (empty($this->predictions)) {
            return 0;
        }
        return max($this->predictions);
    }

    public function getTrend(): string
    {
        if (count($this->predictions) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($this->predictions, 0, (int)(count($this->predictions) / 2));
        $secondHalf = array_slice($this->predictions, (int)(count($this->predictions) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $changePercent = $firstAvg > 0 ? (($secondAvg - $firstAvg) / $firstAvg) * 100 : 0;

        if ($changePercent > 5) {
            return 'increasing';
        } elseif ($changePercent < -5) {
            return 'decreasing';
        }

        return 'stable';
    }

    public function toArray(): array
    {
        return [
            'metric_name' => $this->metricName,
            'predictions' => $this->predictions,
            'confidence_intervals' => $this->confidenceIntervals,
            'confidence_level' => $this->confidenceLevel,
            'model' => $this->model,
            'model_parameters' => $this->modelParameters,
            'accuracy' => $this->accuracy,
            'metadata' => $this->metadata,
            'summary' => [
                'average' => $this->getAveragePrediction(),
                'min' => $this->getMinPrediction(),
                'max' => $this->getMaxPrediction(),
                'trend' => $this->getTrend(),
            ],
        ];
    }
}
