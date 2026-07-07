<?php

namespace Src\Domain\Analytics;

use DateTimeImmutable;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberAnalytics extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $areaId,
        public readonly string $areaName,
        public AnalyticsType $type,
        public TimeRange $timeRange,
        public DateTimeImmutable $generatedAt,
        public int $totalCores,
        public int $usedCores,
        public int $reservedCores,
        public int $availableCores,
        public float $utilizationPercentage,
        public float $averageFiberLength,
        public array $utilizationByCable = [],
        public array $utilizationBySegment = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $areaId,
        string $areaName,
        int $totalCores,
        int $usedCores,
        int $reservedCores = 0
    ): self {
        $available = $totalCores - $usedCores - $reservedCores;
        $utilization = $totalCores > 0 ? ($usedCores / $totalCores) * 100 : 0;

        return new self(
            $id,
            $areaId,
            $areaName,
            AnalyticsType::FIBER_UTILIZATION,
            TimeRange::MONTH,
            new DateTimeImmutable(),
            $totalCores,
            $usedCores,
            $reservedCores,
            $available,
            $utilization
        );
    }

    public function setUtilizationByCable(array $data): void
    {
        $this->utilizationByCable = $data;
    }

    public function setUtilizationBySegment(array $data): void
    {
        $this->utilizationBySegment = $data;
    }

    public function getCapacityStatus(): string
    {
        return match(true) {
            $this->utilizationPercentage >= 90 => 'critical',
            $this->utilizationPercentage >= 75 => 'warning',
            $this->utilizationPercentage >= 50 => 'normal',
            default => 'healthy',
        };
    }

    public function needsCapacityIncrease(): bool
    {
        return $this->utilizationPercentage >= 80;
    }

    public function getEstimatedFullCapacityDays(float $dailyGrowthRate): ?int
    {
        if ($dailyGrowthRate <= 0 || $this->availableCores <= 0) {
            return null;
        }

        return (int) floor($this->availableCores / $dailyGrowthRate);
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
            'total_cores' => $this->totalCores,
            'used_cores' => $this->usedCores,
            'reserved_cores' => $this->reservedCores,
            'available_cores' => $this->availableCores,
            'utilization_percentage' => round($this->utilizationPercentage, 2),
            'average_fiber_length' => round($this->averageFiberLength, 2),
            'capacity_status' => $this->getCapacityStatus(),
            'needs_capacity_increase' => $this->needsCapacityIncrease(),
            'utilization_by_cable' => $this->utilizationByCable,
            'utilization_by_segment' => $this->utilizationBySegment,
            'metadata' => $this->metadata,
        ];
    }
}
