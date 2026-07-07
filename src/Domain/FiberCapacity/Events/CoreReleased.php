<?php

namespace Src\Domain\FiberCapacity\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CoreReleased extends DomainEvent
{
    public function __construct(
        public readonly string $coreId,
        public readonly string $fiberCableId,
        public readonly int $coreNumber,
        public readonly string $releasedFrom,
        public readonly ?string $reason = null
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'fiber.core.released';
    }
}
