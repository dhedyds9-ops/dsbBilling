<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ResourceAllocatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $resourceReservationId,
        public readonly string $serviceInstanceId,
        public readonly string $resourceType,
        public readonly string $resourceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'resource.allocated';
    }
}
