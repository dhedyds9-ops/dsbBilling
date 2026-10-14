<?php

namespace Src\Domain\Outage\ValueObjects;

use DateTimeImmutable;

final class RecoveryTimeEstimate
{
    public function __construct(
        public readonly int $minimumMinutes,
        public readonly int $maximumMinutes,
        public readonly int $averageMinutes,
        public readonly string $method,
        public readonly float $confidence
    ) {}

    public function getMinimumHours(): float
    {
        return $this->minimumMinutes / 60;
    }

    public function getMaximumHours(): float
    {
        return $this->maximumMinutes / 60;
    }

    public function getAverageHours(): float
    {
        return $this->averageMinutes / 60;
    }

    public function getEstimatedCompletion(DateTimeImmutable $startTime): DateTimeImmutable
    {
        return $startTime->modify("+{$this->averageMinutes} minutes");
    }

    public function isWithinSla(int $slaHours): bool
    {
        return $this->maximumMinutes <= ($slaHours * 60);
    }

    public function toArray(): array
    {
        return [
            'minimum_minutes' => $this->minimumMinutes,
            'maximum_minutes' => $this->maximumMinutes,
            'average_minutes' => $this->averageMinutes,
            'method' => $this->method,
            'confidence' => $this->confidence,
        ];
    }

    public static function fromHistory(array $historicalData): self
    {
        if (empty($historicalData)) {
            return new self(30, 120, 60, 'default', 0.5);
        }

        $times = array_column($historicalData, 'recovery_time_minutes');
        $count = count($times);

        $min = min($times);
        $max = max($times);
        $avg = (int) array_sum($times) / $count;
        
        $variance = 0;
        foreach ($times as $time) {
            $variance += pow($time - $avg, 2);
        }
        $stdDev = sqrt($variance / $count);
        $confidence = min(1.0, max(0.0, 1 - ($stdDev / $avg)));

        return new self($min, $max, $avg, 'historical', $confidence);
    }
}
