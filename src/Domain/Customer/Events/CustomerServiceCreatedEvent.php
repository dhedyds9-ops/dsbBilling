<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CustomerServiceCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerServiceId,
        public readonly string $customerId,
        public readonly string $serviceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'customer_service.created';
    }
}
