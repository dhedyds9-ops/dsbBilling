<?php

namespace Src\Domain\Route\Events;

use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Events\DomainEvent;

class AlternativeFound extends DomainEvent
{
    public function __construct(
        public readonly string $routeId,
        public readonly string $originalRouteId,
        public readonly string $sourceNodeId,
        public readonly string $targetNodeId,
        public readonly int $alternativeRank,
        public readonly RouteCost $cost,
        public readonly float $differenceFromShortest,
        public readonly array $alternativeReasons = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'route.alternative_found';
    }

    public function getDifferencePercentage(): float
    {
        return $this->differenceFromShortest;
    }

    public function isViableAlternative(): bool
    {
        return $this->differenceFromShortest <= 50.0;
    }
}
