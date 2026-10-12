<?php

namespace Src\Domain\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaidEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $invoiceId,
        public readonly int $customerId,
        public readonly float $amount,
    ) {}

    public function getName(): string
    {
        return 'invoice.paid';
    }
}
