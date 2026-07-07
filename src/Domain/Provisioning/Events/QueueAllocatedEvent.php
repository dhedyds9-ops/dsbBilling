<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class QueueAllocatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $queueName,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'queue.allocated';
    }
}
