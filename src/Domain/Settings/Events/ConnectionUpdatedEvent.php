<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ConnectionUpdatedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly int|string|null $connectionId,
        public readonly string $action,
        public readonly string $updatedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.connection.updated';
    }
}
