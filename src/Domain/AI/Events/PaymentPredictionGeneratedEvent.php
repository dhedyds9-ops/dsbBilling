<?php

namespace Src\Domain\AI\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PaymentPredictionGeneratedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $invoiceId,
        public readonly float $probability,
        public readonly bool $isLikelyToPay
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ai.payment.prediction_generated';
    }
}
