<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PredictionCompletedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $predictionId,
        public readonly string $predictionType,
        public readonly string $entityId,
        public readonly float $confidence
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.prediction.completed';
    }
}
