<?php

namespace Src\Domain\Route;

use DateTimeImmutable;
use Src\Domain\Route\ValueObjects\Coordinate;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberSegment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $startNodeId,
        public readonly string $startNodeType,
        public readonly string $endNodeId,
        public readonly string $endNodeType,
        public readonly float $distance,
        public readonly float $fiberLength,
        public readonly float $travelTime,
        public bool $isActive = true,
        public bool $isAvailable = true,
        public ?Coordinate $startCoordinate = null,
        public ?Coordinate $endCoordinate = null,
        public ?DateTimeImmutable $lastMaintenance = null,
        public float $capacityUsage = 0.0,
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $startNodeId,
        string $startNodeType,
        string $endNodeId,
        string $endNodeType,
        float $distance,
        float $fiberLength,
        float $travelTime = 0.0
    ): self {
        return new self(
            $id,
            $startNodeId,
            $startNodeType,
            $endNodeId,
            $endNodeType,
            $distance,
            $fiberLength,
            $travelTime ?: ($distance / 60)
        );
    }

    public function getCost(): float
    {
        return $this->distance + ($this->travelTime * 10) + ($this->fiberLength * 1.5);
    }

    public function getLatency(): float
    {
        return $this->fiberLength * 0.000005;
    }

    public function canTraverse(): bool
    {
        return $this->isActive && $this->isAvailable;
    }

    public function setCapacityUsage(float $usage): void
    {
        $this->capacityUsage = min(100, max(0, $usage));
    }

    public function isCongested(): bool
    {
        return $this->capacityUsage >= 80;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function markUnavailable(string $reason): void
    {
        $this->isAvailable = false;
        $this->metadata['unavailable_reason'] = $reason;
        $this->metadata['unavailable_since'] = new DateTimeImmutable();
    }

    public function markAvailable(): void
    {
        $this->isAvailable = true;
        unset($this->metadata['unavailable_reason']);
        unset($this->metadata['unavailable_since']);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'start_node_id' => $this->startNodeId,
            'start_node_type' => $this->startNodeType,
            'end_node_id' => $this->endNodeId,
            'end_node_type' => $this->endNodeType,
            'distance' => round($this->distance, 2),
            'fiber_length' => round($this->fiberLength, 2),
            'travel_time' => round($this->travelTime, 2),
            'is_active' => $this->isActive,
            'is_available' => $this->isAvailable,
            'start_coordinate' => $this->startCoordinate?->toArray(),
            'end_coordinate' => $this->endCoordinate?->toArray(),
            'last_maintenance' => $this->lastMaintenance?->format('Y-m-d H:i:s'),
            'capacity_usage' => round($this->capacityUsage, 2),
            'metadata' => $this->metadata,
        ];
    }
}
