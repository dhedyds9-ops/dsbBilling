<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class RevenueForecastGeneratedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $forecastId,
        public readonly float $predictedRevenue,
        public readonly float $growthRate,
        public readonly int $periods
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.revenue.forecast_generated';
    }
}
