<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceTerminatedEvent extends DomainEvent
{
    public readonly string $customerServiceId;
    public readonly string $customerServiceUuid;
    public readonly string $serviceUuid;

    public function __construct(
        string $customerServiceId,
        public readonly mixed $serviceInstanceId = null,
        public readonly string $reason = 'terminated',
    ) {
        parent::__construct();
        $this->customerServiceId = $customerServiceId;
        $this->customerServiceUuid = $customerServiceId;
        $this->serviceUuid = $customerServiceId;
    }

    public function getName(): string
    {
        return 'service.terminated';
    }
}
