<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class InstallationCompletedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $installationId,
        public readonly string $prospectId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'installation.completed';
    }
}
