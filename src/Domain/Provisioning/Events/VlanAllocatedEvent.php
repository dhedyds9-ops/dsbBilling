<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class VlanAllocatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly int $vlanTag,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'vlan.allocated';
    }
}
