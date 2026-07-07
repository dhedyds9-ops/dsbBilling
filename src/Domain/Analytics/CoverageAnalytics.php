<?php

namespace Src\Domain\Analytics;

use DateTimeImmutable;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\Events\AnalyticsGenerated;
use Src\Domain\Analytics\ValueObjects\CoverageMetric;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageAnalytics extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $areaId,
        public readonly string $areaName,
        public readonly CoverageMetric $metrics,
        public readonly AnalyticsType $type,
        public TimeRange $timeRange,
        public DateTimeImmutable $generatedAt,
        public ?DateTimeImmutable $previousGeneratedAt = null,
        public ?float $previousCoveragePercentage = null,
        public ?float $coverageChange = null,
        public array $coverageBySegment = [],
        public array $coverageByZone = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $areaId,
        string $areaName,
        CoverageMetric $metrics,
        AnalyticsType $type = AnalyticsType::COVERAGE,
        TimeRange $timeRange = TimeRange::MONTH
    ): self {
        return new self(
            $id,
            $areaId,
            $areaName,
            $metrics,
            $type,
            $timeRange,
            new DateTimeImmutable()
        );
    }

    public function setCoverageBySegment(array $segmentData): void
    {
        $this->coverageBySegment = $segmentData;
    }

    public function setCoverageByZone(array $zoneData): void
    {
        $this->coverageByZone = $zoneData;
    }

    public function compareWithPrevious(?CoverageAnalytics $previous): void
    {
        if ($previous && $previous->metrics->coveragePercentage > 0) {
            $this->previousGeneratedAt = $previous->generatedAt;
            $this->previousCoveragePercentage = $previous->metrics->coveragePercentage;
            $this->coverageChange = $this->metrics->coveragePercentage - $previous->metrics->coveragePercentage;
        }
    }

    public function calculateGrowthRate(): float
    {
        if ($this->previousCoveragePercentage === null || $this->previousCoveragePercentage === 0) {
            return 0.0;
        }

        return (($this->metrics->coveragePercentage - $this->previousCoveragePercentage) / $this->previousCoveragePercentage) * 100;
    }

    public function getTrend(): string
    {
        $change = $this->coverageChange ?? 0;
        
        return match(true) {
            $change > 5 => 'significant_growth',
            $change > 0 => 'growth',
            $change < -5 => 'significant_decline',
            $change < 0 => 'decline',
            default => 'stable',
        };
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
            'metrics' => $this->metrics->toArray(),
            'type' => $this->type->value,
            'time_range' => $this->timeRange->value,
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
            'previous_generated_at' => $this->previousGeneratedAt?->format('Y-m-d H:i:s'),
            'coverage_change' => $this->coverageChange,
            'growth_rate' => $this->calculateGrowthRate(),
            'trend' => $this->getTrend(),
            'coverage_by_segment' => $this->coverageBySegment,
            'coverage_by_zone' => $this->coverageByZone,
            'metadata' => $this->metadata,
        ];
    }
}
