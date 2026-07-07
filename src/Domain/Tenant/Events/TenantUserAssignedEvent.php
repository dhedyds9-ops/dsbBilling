<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class TenantUserAssignedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $tenantUserId,
        public readonly string $tenantId,
        public readonly string $userId,
        public readonly array $roles
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'tenant_user.assigned';
    }
}
