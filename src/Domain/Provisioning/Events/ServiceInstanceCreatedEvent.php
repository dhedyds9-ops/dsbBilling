<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceInstanceCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $customerServiceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'service_instance.created';
    }
}
