<?php

namespace Src\Domain\Route;

use DateTimeImmutable;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\ValueObjects\Coordinate;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class NearestNode extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $searchNodeId,
        public readonly string $searchNodeType,
        public readonly string $targetNodeType,
        public readonly Coordinate $searchCoordinate,
        public ?string $foundNodeId = null,
        public ?string $foundNodeName = null,
        public ?Coordinate $foundCoordinate = null,
        public ?float $distance = null,
        public ?float $estimatedTime = null,
        public ?RouteCost $routeCost = null,
        public int $nodesSearched = 0,
        public float $searchRadius = 0.0,
        public AlgorithmType $algorithm = AlgorithmType::DIJKSTRA,
        public ?DateTimeImmutable $searchedAt = null,
        public array $nearbyNodes = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $searchNodeId,
        string $searchNodeType,
        string $targetNodeType,
        Coordinate $searchCoordinate
    ): self {
        return new self(
            $id,
            $searchNodeId,
            $searchNodeType,
            $targetNodeType,
            $searchCoordinate,
            null,
            null,
            null,
            null,
            null,
            null,
            0,
            0.0,
            AlgorithmType::DIJKSTRA,
            new DateTimeImmutable()
        );
    }

    public function setFoundNode(
        string $nodeId,
        string $nodeName,
        Coordinate $coordinate,
        float $distance,
        float $estimatedTime,
        RouteCost $routeCost
    ): void {
        $this->foundNodeId = $nodeId;
        $this->foundNodeName = $nodeName;
        $this->foundCoordinate = $coordinate;
        $this->distance = $distance;
        $this->estimatedTime = $estimatedTime;
        $this->routeCost = $routeCost;
    }

    public function addNearbyNode(
        string $nodeId,
        string $nodeName,
        string $nodeType,
        Coordinate $coordinate,
        float $distance
    ): void {
        $this->nearbyNodes[] = [
            'node_id' => $nodeId,
            'node_name' => $nodeName,
            'node_type' => $nodeType,
            'coordinate' => $coordinate->toArray(),
            'distance' => $distance
        ];
    }

    public function setSearchRadius(float $radius): void
    {
        $this->searchRadius = $radius;
    }

    public function incrementNodesSearched(): void
    {
        $this->nodesSearched++;
    }

    public function isFound(): bool
    {
        return $this->foundNodeId !== null;
    }

    public function getResults(): array
    {
        return [
            'found' => $this->isFound(),
            'node_id' => $this->foundNodeId,
            'node_name' => $this->foundNodeName,
            'distance' => $this->distance,
            'estimated_time' => $this->estimatedTime,
            'route_cost' => $this->routeCost?->toArray(),
            'nodes_searched' => $this->nodesSearched,
            'search_radius' => $this->searchRadius,
        ];
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'search_node_id' => $this->searchNodeId,
            'search_node_type' => $this->searchNodeType,
            'target_node_type' => $this->targetNodeType,
            'search_coordinate' => $this->searchCoordinate->toArray(),
            'found_node_id' => $this->foundNodeId,
            'found_node_name' => $this->foundNodeName,
            'found_coordinate' => $this->foundCoordinate?->toArray(),
            'distance' => $this->distance,
            'estimated_time' => $this->estimatedTime,
            'route_cost' => $this->routeCost?->toArray(),
            'nodes_searched' => $this->nodesSearched,
            'search_radius' => $this->searchRadius,
            'algorithm' => $this->algorithm->value,
            'searched_at' => $this->searchedAt?->format('Y-m-d H:i:s'),
            'nearby_nodes' => $this->nearbyNodes,
            'metadata' => $this->metadata,
        ];
    }
}
