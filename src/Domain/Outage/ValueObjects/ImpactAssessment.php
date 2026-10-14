<?php

namespace Src\Domain\Outage\ValueObjects;

use Src\Domain\Outage\Enums\ImpactLevel;

final class ImpactAssessment
{
    public function __construct(
        public readonly int $totalAffected,
        public readonly int $completelyDown,
        public readonly int $partiallyAffected,
        public readonly int $degradedService,
        public readonly ImpactLevel $overallLevel,
        public readonly float $revenueImpactPerHour,
        public readonly array $affectedServices = []
    ) {}

    public function getServicePercentage(): array
    {
        $total = $this->totalAffected;
        if ($total === 0) {
            return [
                'total' => 0,
                'complete' => 0,
                'partial' => 0,
                'degraded' => 0,
            ];
        }

        return [
            'total' => 100,
            'complete' => ($this->completelyDown / $total) * 100,
            'partial' => ($this->partiallyAffected / $total) * 100,
            'degraded' => ($this->degradedService / $total) * 100,
        ];
    }

    public function estimateDowntimeCost(float $hours): float
    {
        return $this->revenueImpactPerHour * $hours;
    }

    public function toArray(): array
    {
        return [
            'total_affected' => $this->totalAffected,
            'completely_down' => $this->completelyDown,
            'partially_affected' => $this->partiallyAffected,
            'degraded_service' => $this->degradedService,
            'overall_level' => $this->overallLevel->value,
            'revenue_impact_per_hour' => $this->revenueImpactPerHour,
            'affected_services' => $this->affectedServices,
        ];
    }
}
