<?php

namespace Src\Domain\Analytics\ValueObjects;

final class DensityMetric
{
    public function __construct(
        public readonly int $customerCount,
        public readonly float $areaKm2,
        public readonly float $densityPerKm2,
        public readonly float $capacityTotal,
        public readonly float $capacityUsed,
        public readonly float $utilizationPercentage
    ) {}

    public static function calculate(
        int $customerCount,
        float $areaKm2,
        float $capacityTotal,
        float $capacityUsed
    ): self {
        $density = $areaKm2 > 0 ? $customerCount / $areaKm2 : 0;
        $utilization = $capacityTotal > 0 ? ($capacityUsed / $capacityTotal) * 100 : 0;

        return new self(
            $customerCount,
            $areaKm2,
            $density,
            $capacityTotal,
            $capacityUsed,
            $utilization
        );
    }

    public function getDensityLevel(): string
    {
        return match(true) {
            $this->densityPerKm2 >= 100 => 'very_high',
            $this->densityPerKm2 >= 50 => 'high',
            $this->densityPerKm2 >= 20 => 'medium',
            default => 'low',
        };
    }

    public function needsExpansion(): bool
    {
        return $this->utilizationPercentage >= 80 || $this->densityPerKm2 >= 100;
    }

    public function toArray(): array
    {
        return [
            'customer_count' => $this->customerCount,
            'area_km2' => round($this->areaKm2, 2),
            'density_per_km2' => round($this->densityPerKm2, 2),
            'capacity_total' => $this->capacityTotal,
            'capacity_used' => $this->capacityUsed,
            'utilization_percentage' => round($this->utilizationPercentage, 2),
            'density_level' => $this->getDensityLevel(),
            'needs_expansion' => $this->needsExpansion(),
        ];
    }
}
