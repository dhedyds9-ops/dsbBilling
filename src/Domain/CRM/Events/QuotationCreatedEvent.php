<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class QuotationCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $quotationId,
        public readonly string $prospectId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'quotation.created';
    }
}
