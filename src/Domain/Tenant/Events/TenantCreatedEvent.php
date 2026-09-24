<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TenantCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $domain
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'tenant.created';
    }
}
