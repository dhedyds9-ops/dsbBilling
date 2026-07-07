<?php

namespace Src\Domain\Route;

use DateTimeImmutable;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\Route\Enums\RouteType;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RouteCalculation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $sourceNodeId,
        public readonly string $targetNodeId,
        public RouteStatus $status,
        public AlgorithmType $algorithm,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?float $calculationTime = null,
        public ?array $path = null,
        public ?RouteCost $cost = null,
        public ?string $errorMessage = null,
        public array $visitedNodes = [],
        public array $exploredEdges = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $sourceNodeId,
        string $targetNodeId,
        AlgorithmType $algorithm
    ): self {
        return new self(
            $id,
            $sourceNodeId,
            $targetNodeId,
            RouteStatus::CALCULATING,
            $algorithm,
            new DateTimeImmutable()
        );
    }

    public function complete(array $path, RouteCost $cost): void
    {
        $this->status = RouteStatus::CALCULATED;
        $this->completedAt = new DateTimeImmutable();
        $this->calculationTime = ($this->completedAt->getTimestamp() - $this->startedAt->getTimestamp());
        $this->path = $path;
        $this->cost = $cost;
    }

    public function fail(string $errorMessage): void
    {
        $this->status = RouteStatus::FAILED;
        $this->completedAt = new DateTimeImmutable();
        $this->calculationTime = ($this->completedAt->getTimestamp() - $this->startedAt->getTimestamp());
        $this->errorMessage = $errorMessage;
    }

    public function addVisitedNode(string $nodeId, float $distance): void
    {
        $this->visitedNodes[$nodeId] = $distance;
    }

    public function addExploredEdge(string $fromNode, string $toNode, float $cost): void
    {
        $this->exploredEdges[] = [
            'from' => $fromNode,
            'to' => $toNode,
            'cost' => $cost
        ];
    }

    public function getExploredNodeCount(): int
    {
        return count($this->visitedNodes);
    }

    public function getExploredEdgeCount(): int
    {
        return count($this->exploredEdges);
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
            'target_node_id' => $this->targetNodeId,
            'status' => $this->status->value,
            'algorithm' => $this->algorithm->value,
            'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'calculation_time' => $this->calculationTime,
            'path' => $this->path,
            'cost' => $this->cost?->toArray(),
            'error_message' => $this->errorMessage,
            'visited_nodes_count' => $this->getExploredNodeCount(),
            'explored_edges_count' => $this->getExploredEdgeCount(),
            'metadata' => $this->metadata,
        ];
    }
}
