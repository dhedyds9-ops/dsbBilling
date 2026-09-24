<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CompanySettingsUpdatedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly array $changedKeys,
        public readonly string $updatedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.company.updated';
    }
}
