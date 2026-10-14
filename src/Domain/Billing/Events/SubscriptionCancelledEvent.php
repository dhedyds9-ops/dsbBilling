<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelledEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $subscriptionId,
        public readonly int $customerId,
        public readonly ?string $reason = null,
    ) {}

    public function getName(): string
    {
        return 'subscription.cancelled';
    }
}
