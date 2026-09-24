<?php

namespace Src\Domain\Analytics\ValueObjects;

final class CoverageMetric
{
    public function __construct(
        public readonly float $coveredArea,
        public readonly float $totalArea,
        public readonly float $coveragePercentage,
        public readonly int $coveragePoints,
        public readonly int $coveredCustomers,
        public readonly int $totalCustomers,
        public readonly float $customerCoveragePercentage
    ) {}

    public static function calculate(
        float $coveredArea,
        float $totalArea,
        int $coveragePoints,
        int $coveredCustomers,
        int $totalCustomers
    ): self {
        $coveragePercentage = $totalArea > 0 ? ($coveredArea / $totalArea) * 100 : 0;
        $customerCoverage = $totalCustomers > 0 ? ($coveredCustomers / $totalCustomers) * 100 : 0;

        return new self(
            $coveredArea,
            $totalArea,
            $coveragePercentage,
            $coveragePoints,
            $coveredCustomers,
            $totalCustomers,
            $customerCoverage
        );
    }

    public function getCoverageLevel(): string
    {
        return match(true) {
            $this->coveragePercentage >= 90 => 'excellent',
            $this->coveragePercentage >= 70 => 'good',
            $this->coveragePercentage >= 50 => 'moderate',
            default => 'poor',
        };
    }

    public function getUncoveredArea(): float
    {
        return $this->totalArea - $this->coveredArea;
    }

    public function getUncoveredCustomers(): int
    {
        return $this->totalCustomers - $this->coveredCustomers;
    }

    public function toArray(): array
    {
        return [
            'covered_area' => round($this->coveredArea, 2),
            'total_area' => round($this->totalArea, 2),
            'coverage_percentage' => round($this->coveragePercentage, 2),
            'coverage_points' => $this->coveragePoints,
            'covered_customers' => $this->coveredCustomers,
            'total_customers' => $this->totalCustomers,
            'customer_coverage_percentage' => round($this->customerCoveragePercentage, 2),
            'coverage_level' => $this->getCoverageLevel(),
            'uncovered_area' => round($this->getUncoveredArea(), 2),
            'uncovered_customers' => $this->getUncoveredCustomers(),
        ];
    }
}
