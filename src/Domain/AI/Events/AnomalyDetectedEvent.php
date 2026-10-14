<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class AnomalyDetectedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $anomalyId,
        public readonly string $entityId,
        public readonly string $anomalyType,
        public readonly string $severity,
        public readonly float $score
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.anomaly.detected';
    }
}
