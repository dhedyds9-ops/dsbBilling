<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\Enums\KPIType;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\BusinessIntelligence\Enums\TrendDirection;
use Src\Domain\BusinessIntelligence\ValueObjects\MetricValue;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class KPI extends AggregateRoot
{
    private array $currentValue;
    private ?MetricValue $previousPeriodValue = null;
    private ?MetricValue $samePeriodLastYearValue = null;
    private array $breakdown = [];
    private array $thresholds = [];
    private array $targets = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly KPIType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly string $module,
        public readonly DataGranularity $granularity,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {
        $this->currentValue = [];
    }

    public static function create(
        KPIType $type,
        string $name,
        string $description,
        string $module,
        DataGranularity $granularity,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            type: $type,
            name: $name,
            description: $description,
            module: $module,
            granularity: $granularity,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function recordValue(
        float $value,
        ?string $unit = null,
        ?DateTimeImmutable $timestamp = null,
        ?array $breakdown = null
    ): void {
        $this->currentValue = [
            'value' => $value,
            'unit' => $unit ?? $this->type->getUnit(),
            'timestamp' => $timestamp ?? new DateTimeImmutable(),
            'breakdown' => $breakdown,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setThresholds(float $warning, float $critical): void
    {
        $this->thresholds = [
            'warning' => $warning,
            'critical' => $critical,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setTarget(float $target, ?DateTimeImmutable $effectiveFrom = null): void
    {
        $this->targets[] = [
            'target' => $target,
            'effective_from' => $effectiveFrom ?? new DateTimeImmutable(),
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function compareWithPreviousPeriod(float $value, ?string $unit = null): MetricValue
    {
        $previousValue = $this->currentValue['value'] ?? null;

        $this->previousPeriodValue = new MetricValue(
            metricName: $this->type->value,
            value: $value,
            unit: $unit ?? $this->type->getUnit(),
            timestamp: new DateTimeImmutable(),
            previousValue: $previousValue,
            targetValue: $this->getCurrentTarget()
        );

        return $this->previousPeriodValue;
    }

    public function compareWithSamePeriodLastYear(float $value, ?string $unit = null): MetricValue
    {
        $this->samePeriodLastYearValue = new MetricValue(
            metricName: $this->type->value,
            value: $value,
            unit: $unit ?? $this->type->getUnit(),
            timestamp: new DateTimeImmutable(),
            previousValue: $value,
            targetValue: $this->getCurrentTarget()
        );

        return $this->samePeriodLastYearValue;
    }

    public function addBreakdown(string $dimension, array $values): void
    {
        $this->breakdown[$dimension] = $values;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCurrentValue(): ?float
    {
        return $this->currentValue['value'] ?? null;
    }

    public function getCurrentTarget(): ?float
    {
        if (empty($this->targets)) {
            return null;
        }

        $now = new DateTimeImmutable();
        $currentTarget = null;

        foreach ($this->targets as $target) {
            if ($target['effective_from'] <= $now) {
                $currentTarget = $target['target'];
            }
        }

        return $currentTarget;
    }

    public function getTrendDirection(): TrendDirection
    {
        if (!$this->previousPeriodValue) {
            return TrendDirection::STABLE;
        }

        $changePercent = $this->previousPeriodValue->getChangePercentage();

        if ($changePercent === null) {
            return TrendDirection::STABLE;
        }

        if ($changePercent > 2) {
            return TrendDirection::UP;
        } elseif ($changePercent < -2) {
            return TrendDirection::DOWN;
        }

        return TrendDirection::STABLE;
    }

    public function getStatus(): string
    {
        $value = $this->getCurrentValue();
        $target = $this->getCurrentTarget();

        if ($value === null || $target === null) {
            return 'neutral';
        }

        $achievement = ($target > 0) ? ($value / $target) * 100 : 0;

        if ($achievement >= 100) {
            return 'excellent';
        } elseif ($achievement >= 80) {
            return 'good';
        } elseif ($achievement >= 60) {
            return 'warning';
        }

        return 'critical';
    }

    public function getThresholds(): array
    {
        return $this->thresholds;
    }

    public function getBreakdown(): array
    {
        return $this->breakdown;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'type' => $this->type->value,
            'name' => $this->name,
            'description' => $this->description,
            'module' => $this->module,
            'granularity' => $this->granularity->value,
            'current_value' => $this->currentValue,
            'previous_period_value' => $this->previousPeriodValue?->toArray(),
            'same_period_last_year_value' => $this->samePeriodLastYearValue?->toArray(),
            'thresholds' => $this->thresholds,
            'targets' => $this->targets,
            'breakdown' => $this->breakdown,
            'status' => $this->getStatus(),
            'trend' => $this->getTrendDirection()->value,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
