<?php

namespace Src\Domain\Customer\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ServiceActivatedEvent extends DomainEvent
{
    public readonly string $customerServiceId;
    public readonly string $customerServiceUuid;
    public readonly string $serviceUuid;

    public function __construct(
        string $customerServiceId,
        public readonly mixed $serviceInstanceId = null,
        public readonly ?int $userId = null,
    ) {
        parent::__construct();
        $this->customerServiceId = $customerServiceId;
        $this->customerServiceUuid = $customerServiceId;
        $this->serviceUuid = $customerServiceId;
    }

    public function __get(string $name)
    {
        return match ($name) {
            'customerId' => $this->serviceInstanceId,
            default => null,
        };
    }

    public function getName(): string
    {
        return 'service.activated';
    }
}
