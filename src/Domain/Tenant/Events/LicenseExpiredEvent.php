<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class LicenseExpiredEvent extends DomainEvent
{
    public function __construct(
        public readonly string $licenseId,
        public readonly string $tenantId
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'license.expired';
    }
}
