<?php

namespace Src\Domain\Route\ValueObjects;

final class RouteCost
{
    public function __construct(
        public readonly float $distance = 0.0,
        public readonly float $time = 0.0,
        public readonly float $fiberLength = 0.0,
        public readonly int $hopCount = 0,
        public readonly float $latency = 0.0,
        public readonly float $bandwidth = 0.0,
        public readonly float $totalCost = 0.0
    ) {}

    public function add(RouteCost $other): self
    {
        return new self(
            $this->distance + $other->distance,
            $this->time + $other->time,
            $this->fiberLength + $other->fiberLength,
            $this->hopCount + $other->hopCount,
            $this->latency + $other->latency,
            min($this->bandwidth, $other->bandwidth),
            $this->totalCost + $other->totalCost
        );
    }

    public function isBetterThan(RouteCost $other, string $primaryMetric = 'distance'): bool
    {
        return match($primaryMetric) {
            'distance' => $this->distance < $other->distance,
            'time' => $this->time < $other->time,
            'hop' => $this->hopCount < $other->hopCount,
            'cost' => $this->totalCost < $other->totalCost,
            default => $this->distance < $other->distance,
        };
    }

    public function getScore(): float
    {
        return $this->totalCost;
    }

    public function toArray(): array
    {
        return [
            'distance' => round($this->distance, 2),
            'time' => round($this->time, 2),
            'fiber_length' => round($this->fiberLength, 2),
            'hop_count' => $this->hopCount,
            'latency' => round($this->latency, 2),
            'bandwidth' => round($this->bandwidth, 2),
            'total_cost' => round($this->totalCost, 2),
        ];
    }

    public static function fromSegment(
        float $fiberLength,
        float $travelTime,
        int $hopCount = 1
    ): self {
        $latency = $fiberLength * 0.000005;
        $distance = $fiberLength;

        return new self(
            distance: $distance,
            time: $travelTime,
            fiberLength: $fiberLength,
            hopCount: $hopCount,
            latency: $latency,
            bandwidth: 10000,
            totalCost: $fiberLength + ($travelTime * 10) + ($hopCount * 50)
        );
    }
}
