<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly int $contractId = 0,
    ) {}

    public function getName(): string
    {
        return 'invoice.created';
    }
}
