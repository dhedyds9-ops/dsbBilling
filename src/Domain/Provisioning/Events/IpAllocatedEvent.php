<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class IpAllocatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $ipAddress,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'ip.allocated';
    }
}
