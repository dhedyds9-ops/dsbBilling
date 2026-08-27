<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PaymentGatewayUpdatedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $gatewayKey,
        public readonly string $action,
        public readonly string $updatedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.payment_gateway.updated';
    }
}
