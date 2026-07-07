<?php

namespace Src\Domain\FiberCapacity\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class SplitterFull extends DomainEvent
{
    public function __construct(
        public readonly string $splitterId,
        public readonly string $splitterCode,
        public readonly int $totalPorts,
        public readonly int $usedPorts,
        public readonly float $utilizationPercentage
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'fiber.splitter.full';
    }

    public function getAvailablePorts(): int
    {
        return $this->totalPorts - $this->usedPorts;
    }

    public function isCompletelyFull(): bool
    {
        return $this->usedPorts >= $this->totalPorts;
    }
}
