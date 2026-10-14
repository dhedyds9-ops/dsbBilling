<?php

namespace Src\Domain\FiberCapacity\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CoreAllocated extends DomainEvent
{
    public function __construct(
        public readonly string $coreId,
        public readonly string $fiberCableId,
        public readonly int $coreNumber,
        public readonly string $allocatedTo,
        public readonly string $allocationType
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'fiber.core.allocated';
    }
}
