<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

use DateTimeImmutable;

class MetricValue
{
    public function __construct(
        public readonly string $metricName,
        public readonly float $value,
        public readonly ?string $unit = null,
        public readonly ?DateTimeImmutable $timestamp = null,
        public readonly ?float $previousValue = null,
        public readonly ?float $targetValue = null,
        public readonly ?array $breakdown = null,
        public readonly ?array $metadata = null
    ) {}

    public function getChangePercentage(): ?float
    {
        if ($this->previousValue === null || $this->previousValue == 0) {
            return null;
        }
        return (($this->value - $this->previousValue) / $this->previousValue) * 100;
    }

    public function getChangeAbsolute(): ?float
    {
        if ($this->previousValue === null) {
            return null;
        }
        return $this->value - $this->previousValue;
    }

    public function isAboveTarget(): ?bool
    {
        if ($this->targetValue === null) {
            return null;
        }
        return $this->value >= $this->targetValue;
    }

    public function getAchievementPercentage(): ?float
    {
        if ($this->targetValue === null || $this->targetValue == 0) {
            return null;
        }
        return ($this->value / $this->targetValue) * 100;
    }

    public function getStatus(): string
    {
        if ($this->targetValue === null) {
            return 'neutral';
        }

        $achievement = $this->getAchievementPercentage();

        if ($achievement >= 100) {
            return 'excellent';
        } elseif ($achievement >= 80) {
            return 'good';
        } elseif ($achievement >= 60) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    public function toArray(): array
    {
        return [
            'metric_name' => $this->metricName,
            'value' => $this->value,
            'unit' => $this->unit,
            'timestamp' => $this->timestamp?->format('Y-m-d H:i:s'),
            'previous_value' => $this->previousValue,
            'target_value' => $this->targetValue,
            'change_percentage' => $this->getChangePercentage(),
            'change_absolute' => $this->getChangeAbsolute(),
            'achievement_percentage' => $this->getAchievementPercentage(),
            'status' => $this->getStatus(),
            'breakdown' => $this->breakdown,
            'metadata' => $this->metadata,
        ];
    }
}
