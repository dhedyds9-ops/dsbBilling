<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $subscriptionId,
        public readonly int $customerId,
        public readonly ?int $customerServiceId = null,
    ) {}

    public function getName(): string
    {
        return 'subscription.created';
    }
}
