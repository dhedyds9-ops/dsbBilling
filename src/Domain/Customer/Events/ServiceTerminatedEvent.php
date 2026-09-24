<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceTerminatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerServiceId,
        public readonly string $serviceInstanceId,
        public readonly string $reason,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'service.terminated';
    }
}
