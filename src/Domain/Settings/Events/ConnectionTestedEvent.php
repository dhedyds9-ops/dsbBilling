<?php

namespace Src\Domain\Settings\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class ConnectionTestedEvent extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly int|string $connectionId,
        public readonly bool $success,
        public readonly ?string $message,
        public readonly string $testedAt,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'settings.connection.tested';
    }
}
