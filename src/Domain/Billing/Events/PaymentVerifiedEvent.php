<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerifiedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $paymentId,
        public readonly int $customerId,
        public readonly float $amount = 0.0,
        public readonly ?string $invoiceId = null,
    ) {}

    public function getName(): string
    {
        return 'payment.verified';
    }
}
