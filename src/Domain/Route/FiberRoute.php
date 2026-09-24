<?php

namespace Src\Domain\Route;

use DateTimeImmutable;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\Route\Enums\RouteType;
use Src\Domain\Route\Events\RouteCalculated;
use Src\Domain\Route\Events\RouteOptimized;
use Src\Domain\Route\ValueObjects\PathHop;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberRoute extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $sourceNodeId,
        public readonly string $sourceNodeType,
        public readonly string $targetNodeId,
        public readonly string $targetNodeType,
        public RouteStatus $status,
        public RouteType $routeType,
        public AlgorithmType $algorithm,
        public array $hops = [],
        public ?RouteCost $cost = null,
        public ?DateTimeImmutable $calculatedAt = null,
        public ?DateTimeImmutable $optimizedAt = null,
        public bool $isActive = false,
        public array $alternativeRoutes = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $sourceNodeId,
        string $sourceNodeType,
        string $targetNodeId,
        string $targetNodeType,
        RouteType $routeType,
        AlgorithmType $algorithm
    ): self {
        return new self(
            $id,
            $sourceNodeId,
            $sourceNodeType,
            $targetNodeId,
            $targetNodeType,
            RouteStatus::PENDING,
            $routeType,
            $algorithm
        );
    }

    public function setRoute(array $hops, RouteCost $cost, float $calculationTime): void
    {
        $this->hops = $hops;
        $this->cost = $cost;
        $this->status = RouteStatus::CALCULATED;
        $this->calculatedAt = new DateTimeImmutable();

        $this->recordThat(new RouteCalculated(
            $this->id->value,
            $this->sourceNodeId,
            $this->targetNodeId,
            $this->routeType,
            $this->algorithm,
            $cost->hopCount,
            $cost,
            $calculationTime
        ));
    }

    public function optimize(array $optimizationSteps, float $previousCost, float $newCost): void
    {
        $hopReduction = $this->cost->hopCount - count($this->hops);
        
        $this->optimizedAt = new DateTimeImmutable();
        $this->status = RouteStatus::OPTIMIZED;

        $this->recordThat(new RouteOptimized(
            $this->id->value,
            $this->id->value,
            $hopReduction,
            $previousCost - $newCost,
            $previousCost,
            $newCost,
            $optimizationSteps
        ));
    }

    public function addAlternativeRoute(FiberRoute $alternative): void
    {
        $this->alternativeRoutes[] = [
            'route_id' => $alternative->id->value,
            'cost' => $alternative->cost?->toArray() ?? [],
            'hop_count' => $alternative->cost?->hopCount ?? 0,
        ];
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->status = RouteStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->status = RouteStatus::DEACTIVATED;
    }

    public function markFailed(string $reason): void
    {
        $this->status = RouteStatus::FAILED;
        $this->metadata['failure_reason'] = $reason;
        $this->metadata['failed_at'] = new DateTimeImmutable();
    }

    public function getHopCount(): int
    {
        return $this->cost?->hopCount ?? count($this->hops);
    }

    public function getTotalDistance(): float
    {
        return $this->cost?->distance ?? 0.0;
    }

    public function getTotalFiberLength(): float
    {
        return $this->cost?->fiberLength ?? 0.0;
    }

    public function getEstimatedTime(): float
    {
        return $this->cost?->time ?? 0.0;
    }

    public function getTotalCost(): float
    {
        return $this->cost?->totalCost ?? 0.0;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'source_node_id' => $this->sourceNodeId,
            'source_node_type' => $this->sourceNodeType,
            'target_node_id' => $this->targetNodeId,
            'target_node_type' => $this->targetNodeType,
            'status' => $this->status->value,
            'route_type' => $this->routeType->value,
            'algorithm' => $this->algorithm->value,
            'hops' => array_map(fn($h) => $h instanceof PathHop ? $h->toArray() : $h, $this->hops),
            'cost' => $this->cost?->toArray(),
            'calculated_at' => $this->calculatedAt?->format('Y-m-d H:i:s'),
            'optimized_at' => $this->optimizedAt?->format('Y-m-d H:i:s'),
            'is_active' => $this->isActive,
            'alternative_routes' => $this->alternativeRoutes,
            'metadata' => $this->metadata,
        ];
    }
}
