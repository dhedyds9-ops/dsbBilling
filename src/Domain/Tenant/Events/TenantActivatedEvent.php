<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TenantActivatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'tenant.activated';
    }
}
