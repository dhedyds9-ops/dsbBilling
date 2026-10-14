<?php

namespace Src\Domain\Analytics\Events;

use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\SharedKernel\Events\DomainEvent;

class AnalyticsGenerated extends DomainEvent
{
    public function __construct(
        public readonly string $analyticsId,
        public readonly AnalyticsType $type,
        public readonly string $areaId,
        public readonly array $metrics,
        public readonly float $generationTime
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'analytics.generated';
    }

    public function getSummary(): string
    {
        return "Analytics {$this->type->label()} generated for area {$this->areaId}";
    }
}
