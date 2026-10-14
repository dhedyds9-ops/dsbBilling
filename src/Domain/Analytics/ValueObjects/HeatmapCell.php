<?php

namespace Src\Domain\Analytics\ValueObjects;

final class HeatmapCell
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly float $centerLat,
        public readonly float $centerLon,
        public readonly float $value,
        public readonly float $intensity,
        public readonly string $color,
        public readonly int $customerCount = 0,
        public readonly float $capacityUsage = 0.0,
        public readonly array $metadata = []
    ) {}

    public function getSeverity(): string
    {
        return match(true) {
            $this->value >= 90 => 'critical',
            $this->value >= 75 => 'warning',
            $this->value >= 50 => 'moderate',
            default => 'low',
        };
    }

    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'center_lat' => $this->centerLat,
            'center_lon' => $this->centerLon,
            'value' => round($this->value, 2),
            'intensity' => round($this->intensity, 2),
            'color' => $this->color,
            'customer_count' => $this->customerCount,
            'capacity_usage' => round($this->capacityUsage, 2),
            'severity' => $this->getSeverity(),
            'metadata' => $this->metadata,
        ];
    }
}
