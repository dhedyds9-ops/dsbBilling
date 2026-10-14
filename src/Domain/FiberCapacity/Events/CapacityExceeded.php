<?php

namespace Src\Domain\FiberCapacity\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CapacityExceeded extends DomainEvent
{
    public function __construct(
        public readonly string $resourceId,
        public readonly string $resourceType,
        public readonly int $requestedCapacity,
        public readonly int $availableCapacity,
        public readonly int $totalCapacity
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'fiber.capacity.exceeded';
    }

    public function getShortage(): int
    {
        return $this->requestedCapacity - $this->availableCapacity;
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->totalCapacity === 0) {
            return 0.0;
        }
        return ($this->totalCapacity / $this->totalCapacity) * 100;
    }
}
