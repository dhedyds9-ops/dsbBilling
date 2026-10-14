<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CustomerActivatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $activationId,
        public readonly string $customerServiceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'customer.activated';
    }
}
