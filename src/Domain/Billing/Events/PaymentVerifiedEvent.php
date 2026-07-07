<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PaymentVerifiedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $invoiceId,
        public readonly int $customerId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'payment.verified';
    }
}
