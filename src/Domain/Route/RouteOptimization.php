<?php

namespace Src\Domain\Route;

use DateTimeImmutable;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RouteOptimization extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $routeId,
        public readonly string $sourceNodeId,
        public readonly string $targetNodeId,
        public RouteStatus $status,
        public ?RouteCost $originalCost = null,
        public ?RouteCost $optimizedCost = null,
        public ?array $originalPath = null,
        public ?array $optimizedPath = null,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public array $optimizationSteps = [],
        public array $potentialImprovements = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $routeId,
        string $sourceNodeId,
        string $targetNodeId
    ): self {
        return new self(
            $id,
            $routeId,
            $sourceNodeId,
            $targetNodeId,
            RouteStatus::CALCULATING,
            null,
            null,
            null,
            null,
            new DateTimeImmutable()
        );
    }

    public function setOriginalRoute(array $path, RouteCost $cost): void
    {
        $this->originalPath = $path;
        $this->originalCost = $cost;
    }

    public function addOptimizationStep(string $description, float $improvement): void
    {
        $this->optimizationSteps[] = [
            'description' => $description,
            'improvement' => $improvement,
            'timestamp' => new DateTimeImmutable()
        ];
    }

    public function complete(array $optimizedPath, RouteCost $optimizedCost): void
    {
        $this->status = RouteStatus::CALCULATED;
        $this->completedAt = new DateTimeImmutable();
        $this->optimizedPath = $optimizedPath;
        $this->optimizedCost = $optimizedCost;
    }

    public function fail(string $reason): void
    {
        $this->status = RouteStatus::FAILED;
        $this->completedAt = new DateTimeImmutable();
        $this->metadata['failure_reason'] = $reason;
    }

    public function addPotentialImprovement(string $type, array $details): void
    {
        $this->potentialImprovements[] = [
            'type' => $type,
            'details' => $details,
            'identified_at' => new DateTimeImmutable()
        ];
    }

    public function getCostReduction(): ?float
    {
        if ($this->originalCost === null || $this->optimizedCost === null) {
            return null;
        }
        return $this->originalCost->totalCost - $this->optimizedCost->totalCost;
    }

    public function getCostReductionPercentage(): ?float
    {
        if ($this->originalCost === null || $this->originalCost->totalCost === 0) {
            return null;
        }
        return ($this->getCostReduction() / $this->originalCost->totalCost) * 100;
    }

    public function getHopReduction(): ?int
    {
        if ($this->originalCost === null || $this->optimizedCost === null) {
            return null;
        }
        return $this->originalCost->hopCount - $this->optimizedCost->hopCount;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'route_id' => $this->routeId->value,
            'source_node_id' => $this->sourceNodeId,
            'target_node_id' => $this->targetNodeId,
            'status' => $this->status->value,
            'original_cost' => $this->originalCost?->toArray(),
            'optimized_cost' => $this->optimizedCost?->toArray(),
            'optimization_steps' => $this->optimizationSteps,
            'potential_improvements' => $this->potentialImprovements,
            'cost_reduction' => $this->getCostReduction(),
            'cost_reduction_percentage' => $this->getCostReductionPercentage(),
            'hop_reduction' => $this->getHopReduction(),
            'metadata' => $this->metadata,
        ];
    }
}
