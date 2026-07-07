<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetDepreciation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly string $method,
        public readonly float $originalValue,
        public readonly float $salvageValue,
        public readonly int $usefulLifeMonths,
        public readonly float $currentValue,
        public readonly int $accumulatedDepreciation,
        public readonly DateTimeImmutable $calculatedAt,
        public readonly ?DateTimeImmutable $nextCalculationAt = null
    ) {}

    public const METHOD_STRAIGHT_LINE = 'straight_line';
    public const METHOD_DECLINING_BALANCE = 'declining_balance';
    public const METHOD_SUM_OF_YEARS = 'sum_of_years';

    public static function calculate(
        Uuid $assetId,
        float $originalValue,
        float $salvageValue,
        int $usefulLifeMonths,
        string $method = self::METHOD_STRAIGHT_LINE,
        ?DateTimeImmutable $asOfDate = null
    ): self {
        $asOfDate = $asOfDate ?? new DateTimeImmutable();
        $monthsElapsed = 0;
        $accumulatedDepreciation = 0;
        $currentValue = $originalValue;

        switch ($method) {
            case self::METHOD_STRAIGHT_LINE:
                $monthlyDepreciation = ($originalValue - $salvageValue) / $usefulLifeMonths;
                $accumulatedDepreciation = $monthlyDepreciation * $monthsElapsed;
                $currentValue = max($salvageValue, $originalValue - $accumulatedDepreciation);
                break;

            case self::METHOD_DECLINING_BALANCE:
                $rate = 2 / $usefulLifeMonths;
                for ($i = 0; $i < $monthsElapsed; $i++) {
                    $depreciation = $currentValue * $rate / 12;
                    $currentValue -= $depreciation;
                    $accumulatedDepreciation += $depreciation;
                }
                $currentValue = max($salvageValue, $currentValue);
                break;
        }

        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            method: $method,
            originalValue: $originalValue,
            salvageValue: $salvageValue,
            usefulLifeMonths: $usefulLifeMonths,
            currentValue: $currentValue,
            accumulatedDepreciation: $accumulatedDepreciation,
            calculatedAt: $asOfDate,
            nextCalculationAt: $asOfDate->modify('+1 month')
        );
    }

    public function getDepreciationRate(): float
    {
        if ($this->originalValue === 0) {
            return 0;
        }
        return ($this->accumulatedDepreciation / $this->originalValue) * 100;
    }

    public function getRemainingValue(): float
    {
        return max(0, $this->originalValue - $this->accumulatedDepreciation - $this->salvageValue);
    }
}
