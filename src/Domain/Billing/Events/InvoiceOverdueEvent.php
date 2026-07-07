<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class InvoiceOverdueEvent extends DomainEvent
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly float $outstandingAmount,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'invoice.overdue';
    }
}
