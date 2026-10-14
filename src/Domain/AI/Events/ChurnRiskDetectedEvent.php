<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ChurnRiskDetectedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerId,
        public readonly float $churnScore,
        public readonly string $riskLevel,
        public readonly array $riskFactors
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.churn.risk_detected';
    }
}
