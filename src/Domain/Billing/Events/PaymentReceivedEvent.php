<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PaymentReceivedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly float $amount,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'payment.received';
    }
}
