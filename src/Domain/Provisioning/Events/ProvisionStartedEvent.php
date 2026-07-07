<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ProvisionStartedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $provisionPipelineId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'provision.started';
    }
}
