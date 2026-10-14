<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ProvisioningFailedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $stepName,
        public readonly string $errorMessage,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'provisioning.failed';
    }
}
