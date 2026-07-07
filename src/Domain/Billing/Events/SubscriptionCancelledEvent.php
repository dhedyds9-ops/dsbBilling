<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class SubscriptionCancelledEvent extends DomainEvent
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly int $customerId,
        public readonly string $reason,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'subscription.cancelled';
    }
}
