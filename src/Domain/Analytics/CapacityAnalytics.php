<?php

namespace Src\Domain\Analytics;

use DateTimeImmutable;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CapacityAnalytics extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $areaId,
        public readonly string $areaName,
        public AnalyticsType $type,
        public TimeRange $timeRange,
        public DateTimeImmutable $generatedAt,
        public int $totalOdp,
        public int $activeOdp,
        public int $totalOlt,
        public int $activeOlt,
        public int $totalPonPorts,
        public int $usedPonPorts,
        public int $totalOnuCapacity,
        public int $usedOnuCapacity,
        public float $odpUtilization,
        public float $oltUtilization,
        public float $ponUtilization,
        public float $onuUtilization,
        public array $odpDetails = [],
        public array $oltDetails = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $areaId,
        string $areaName
    ): self {
        return new self(
            $id,
            $areaId,
            $areaName,
            AnalyticsType::CAPACITY_ANALYTICS ?? AnalyticsType::COVERAGE,
            TimeRange::MONTH,
            new DateTimeImmutable(),
            0, 0, 0, 0, 0, 0, 0, 0,
            0.0, 0.0, 0.0, 0.0
        );
    }

    public function calculateUtilization(): void
    {
        $this->odpUtilization = $this->totalOdp > 0 ? (($this->totalOdp - $this->activeOdp) / $this->totalOdp) * 100 : 0;
        $this->oltUtilization = $this->totalOlt > 0 ? (($this->totalOlt - $this->activeOlt) / $this->totalOlt) * 100 : 0;
        $this->ponUtilization = $this->totalPonPorts > 0 ? ($this->usedPonPorts / $this->totalPonPorts) * 100 : 0;
        $this->onuUtilization = $this->totalOnuCapacity > 0 ? ($this->usedOnuCapacity / $this->totalOnuCapacity) * 100 : 0;
    }

    public function getCriticalResources(): array
    {
        $critical = [];

        if ($this->odpUtilization >= 90) {
            $critical[] = ['type' => 'odp', 'utilization' => $this->odpUtilization];
        }
        if ($this->oltUtilization >= 90) {
            $critical[] = ['type' => 'olt', 'utilization' => $this->oltUtilization];
        }
        if ($this->ponUtilization >= 90) {
            $critical[] = ['type' => 'pon', 'utilization' => $this->ponUtilization];
        }
        if ($this->onuUtilization >= 90) {
            $critical[] = ['type' => 'onu', 'utilization' => $this->onuUtilization];
        }

        return $critical;
    }

    public function getWarningResources(): array
    {
        $warning = [];

        if ($this->odpUtilization >= 75 && $this->odpUtilization < 90) {
            $warning[] = ['type' => 'odp', 'utilization' => $this->odpUtilization];
        }
        if ($this->oltUtilization >= 75 && $this->oltUtilization < 90) {
            $warning[] = ['type' => 'olt', 'utilization' => $this->oltUtilization];
        }
        if ($this->ponUtilization >= 75 && $this->ponUtilization < 90) {
            $warning[] = ['type' => 'pon', 'utilization' => $this->ponUtilization];
        }
        if ($this->onuUtilization >= 75 && $this->onuUtilization < 90) {
            $warning[] = ['type' => 'onu', 'utilization' => $this->onuUtilization];
        }

        return $warning;
    }

    public function getOverallHealthScore(): float
    {
        $scores = [];
        
        if ($this->totalOdp > 0) {
            $scores[] = max(0, 100 - $this->odpUtilization);
        }
        if ($this->totalOlt > 0) {
            $scores[] = max(0, 100 - $this->oltUtilization);
        }
        if ($this->totalPonPorts > 0) {
            $scores[] = max(0, 100 - $this->ponUtilization);
        }
        if ($this->totalOnuCapacity > 0) {
            $scores[] = max(0, 100 - $this->onuUtilization);
        }

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 100.0;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'area_id' => $this->areaId,
            'area_name' => $this->areaName,
            'type' => $this->type->value,
            'time_range' => $this->timeRange->value,
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
            'odp' => [
                'total' => $this->totalOdp,
                'active' => $this->activeOdp,
                'utilization' => round($this->odpUtilization, 2),
            ],
            'olt' => [
                'total' => $this->totalOlt,
                'active' => $this->activeOlt,
                'utilization' => round($this->oltUtilization, 2),
            ],
            'pon' => [
                'total' => $this->totalPonPorts,
                'used' => $this->usedPonPorts,
                'utilization' => round($this->ponUtilization, 2),
            ],
            'onu' => [
                'total' => $this->totalOnuCapacity,
                'used' => $this->usedOnuCapacity,
                'utilization' => round($this->onuUtilization, 2),
            ],
            'critical_resources' => $this->getCriticalResources(),
            'warning_resources' => $this->getWarningResources(),
            'health_score' => round($this->getOverallHealthScore(), 2),
            'odp_details' => $this->odpDetails,
            'olt_details' => $this->oltDetails,
            'metadata' => $this->metadata,
        ];
    }
}
