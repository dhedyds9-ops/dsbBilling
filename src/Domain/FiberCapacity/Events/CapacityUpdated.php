<?php

namespace Src\Domain\FiberCapacity\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class CapacityUpdated extends DomainEvent
{
    public function __construct(
        public readonly string $resourceId,
        public readonly string $resourceType,
        public readonly int $previousUsed,
        public readonly int $currentUsed,
        public readonly int $totalCapacity,
        public readonly float $previousPercentage,
        public readonly float $currentPercentage
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'fiber.capacity.updated';
    }

    public function getChangePercentage(): float
    {
        return $this->currentPercentage - $this->previousPercentage;
    }

    public function isIncrease(): bool
    {
        return $this->currentUsed > $this->previousUsed;
    }
}
