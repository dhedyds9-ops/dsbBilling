<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CustomerSuspendedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'customer.suspended';
    }
}
