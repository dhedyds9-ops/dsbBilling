<?php

namespace Src\Domain\Route\Events;

use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Events\DomainEvent;

class RouteOptimized extends DomainEvent
{
    public function __construct(
        public readonly string $routeId,
        public readonly string $previousRouteId,
        public readonly int $hopCountReduction,
        public readonly float $costReduction,
        public readonly float $previousCost,
        public readonly float $newCost,
        public readonly array $optimizationSteps = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'route.optimized';
    }

    public function getImprovementPercentage(): float
    {
        if ($this->previousCost === 0) {
            return 0.0;
        }
        return (($this->previousCost - $this->newCost) / $this->previousCost) * 100;
    }
}
