<?php

namespace Src\Domain\Billing\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class JournalPostedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $journalId,
        public readonly string $referenceType,
        public readonly string $referenceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'journal.posted';
    }
}
