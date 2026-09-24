<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class SubscriptionCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly int $customerId,
        public readonly int $customerServiceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'subscription.created';
    }
}
