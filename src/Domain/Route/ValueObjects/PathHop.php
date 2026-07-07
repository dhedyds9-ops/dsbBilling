<?php

namespace Src\Domain\Route\ValueObjects;

use DateTimeImmutable;

final class PathHop
{
    public function __construct(
        public readonly int $sequence,
        public readonly string $nodeId,
        public readonly string $nodeType,
        public readonly string $nodeName,
        public readonly float $distanceFromPrevious,
        public readonly float $fiberLength,
        public readonly float $cumulativeDistance,
        public readonly float $cumulativeFiberLength,
        public readonly float $arrivalTime,
        public readonly ?Coordinate $coordinate = null,
        public readonly array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'sequence' => $this->sequence,
            'node_id' => $this->nodeId,
            'node_type' => $this->nodeType,
            'node_name' => $this->nodeName,
            'distance_from_previous' => round($this->distanceFromPrevious, 2),
            'fiber_length' => round($this->fiberLength, 2),
            'cumulative_distance' => round($this->cumulativeDistance, 2),
            'cumulative_fiber_length' => round($this->cumulativeFiberLength, 2),
            'arrival_time' => round($this->arrivalTime, 2),
            'coordinate' => $this->coordinate?->toArray(),
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sequence: (int) $data['sequence'],
            nodeId: $data['node_id'],
            nodeType: $data['node_type'],
            nodeName: $data['node_name'],
            distanceFromPrevious: (float) ($data['distance_from_previous'] ?? 0),
            fiberLength: (float) ($data['fiber_length'] ?? 0),
            cumulativeDistance: (float) ($data['cumulative_distance'] ?? 0),
            cumulativeFiberLength: (float) ($data['cumulative_fiber_length'] ?? 0),
            arrivalTime: (float) ($data['arrival_time'] ?? 0),
            coordinate: isset($data['coordinate']) ? Coordinate::fromArray($data['coordinate']) : null,
            metadata: $data['metadata'] ?? [],
        );
    }
}
