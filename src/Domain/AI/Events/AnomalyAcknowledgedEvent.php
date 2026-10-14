<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class AnomalyAcknowledgedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $anomalyId,
        public readonly string $acknowledgedBy,
        public readonly ?string $notes = null
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.anomaly.acknowledged';
    }
}
