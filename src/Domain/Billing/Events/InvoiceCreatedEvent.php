<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class InvoiceCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly int $contractId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'invoice.created';
    }
}
