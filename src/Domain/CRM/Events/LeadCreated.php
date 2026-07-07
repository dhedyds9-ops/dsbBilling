<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class LeadCreated extends DomainEvent
{
    public function __construct(
        public readonly string $leadId,
        public readonly string $userId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'lead.created';
    }
}
