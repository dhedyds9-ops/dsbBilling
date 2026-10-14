<?php

namespace Src\Domain\Route\Events;

use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\Enums\RouteType;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Events\DomainEvent;

class RouteCalculated extends DomainEvent
{
    public function __construct(
        public readonly string $routeId,
        public readonly string $sourceNodeId,
        public readonly string $targetNodeId,
        public readonly RouteType $routeType,
        public readonly AlgorithmType $algorithm,
        public readonly int $hopCount,
        public readonly RouteCost $cost,
        public readonly float $calculationTime
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'route.calculated';
    }

    public function getSummary(): string
    {
        return "Route calculated from {$this->sourceNodeId} to {$this->targetNodeId}: {$this->hopCount} hops, cost: {$this->cost->totalCost}";
    }
}
