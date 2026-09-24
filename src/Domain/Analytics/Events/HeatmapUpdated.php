<?php

namespace Src\Domain\Analytics\Events;

use Src\Domain\Analytics\Enums\HeatmapLayer;
use Src\Domain\SharedKernel\Events\DomainEvent;

class HeatmapUpdated extends DomainEvent
{
    public function __construct(
        public readonly string $heatmapId,
        public readonly HeatmapLayer $layer,
        public readonly string $areaId,
        public readonly int $cellCount,
        public readonly int $customerCount,
        public readonly float $maxValue,
        public readonly float $minValue
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'analytics.heatmap_updated';
    }

    public function getValueRange(): float
    {
        return $this->maxValue - $this->minValue;
    }
}
