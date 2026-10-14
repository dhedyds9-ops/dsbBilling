<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ResourcesReservedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly array $reservedResources,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'resources.reserved';
    }
}
