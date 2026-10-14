<?php

namespace Src\Domain\Provisioning\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class DeviceAssignedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $serviceInstanceId,
        public readonly string $deviceId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'device.assigned';
    }
}
