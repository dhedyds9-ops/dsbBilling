<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ModelTrainedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $modelId,
        public readonly string $modelType,
        public readonly float $accuracy,
        public readonly array $metrics
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.model.trained';
    }
}
