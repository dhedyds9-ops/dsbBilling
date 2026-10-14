<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ProspectCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $prospectId,
        public readonly string $prospectName,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'prospect.created';
    }
}
