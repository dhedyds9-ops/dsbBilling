<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class LicenseExpiringEvent extends DomainEvent
{
    public function __construct(
        public readonly string $licenseId,
        public readonly string $tenantId,
        public readonly int $remainingDays
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'license.expiring';
    }
}
