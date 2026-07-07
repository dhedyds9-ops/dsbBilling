<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class LeadCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $leadId,
        public readonly string $leadName,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'lead.created';
    }
}
