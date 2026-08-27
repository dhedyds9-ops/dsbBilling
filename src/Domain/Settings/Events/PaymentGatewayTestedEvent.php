<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PaymentGatewayTestedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $gatewayKey,
        public readonly bool $success,
        public readonly ?string $message,
        public readonly string $testedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.payment_gateway.tested';
    }
}
