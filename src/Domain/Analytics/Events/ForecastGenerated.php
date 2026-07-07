<?php

namespace Src\Domain\Analytics\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ForecastGenerated extends DomainEvent
{
    public function __construct(
        public readonly string $forecastId,
        public readonly string $areaId,
        public readonly string $metricType,
        public readonly int $forecastDays,
        public readonly array $predictions,
        public readonly array $confidence,
        public readonly float $growthRate
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'analytics.forecast_generated';
    }

    public function getHighConfidencePredictions(): array
    {
        return array_filter($this->predictions, fn($c) => $c >= 0.8);
    }
}
