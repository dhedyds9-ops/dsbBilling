<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceSuspendedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $customerServiceId,
        public readonly string $reason,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'service.suspended';
    }
}
