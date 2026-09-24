<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class RollbackStartedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly array $stepsToRollback,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'rollback.started';
    }
}
