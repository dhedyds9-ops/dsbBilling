<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CapacityExceededEvent extends DomainEvent
{
    public function __construct(
        public readonly string $resourceId,
        public readonly string $resourceType,
        public readonly int $requestedCapacity,
        public readonly int $availableCapacity,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'capacity.exceeded';
    }
}
