<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceReactivatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerServiceId,
        public readonly string $serviceInstanceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'service.reactivated';
    }
}
