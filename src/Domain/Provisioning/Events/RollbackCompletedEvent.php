<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class RollbackCompletedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'rollback.completed';
    }
}
