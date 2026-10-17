<?php

namespace Src\Domain\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentGatewayUpdatedEvent
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public string $gatewayKey,
        public string $action,
        public string $updatedAt
    ) {}
}
