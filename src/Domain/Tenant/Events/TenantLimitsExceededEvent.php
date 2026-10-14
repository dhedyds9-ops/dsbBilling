<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TenantLimitsExceededEvent extends DomainEvent
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $limitType,
        public readonly int $currentValue,
        public readonly int $limitValue
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'tenant.limits_exceeded';
    }
}
