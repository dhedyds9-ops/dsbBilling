<?php

namespace Src\Domain\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentGatewayTestedEvent
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public string $gatewayKey,
        public bool $success,
        public string $message,
        public string $testedAt
    ) {}
}
