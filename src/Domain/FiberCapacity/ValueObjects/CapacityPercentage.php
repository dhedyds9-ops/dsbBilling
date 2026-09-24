<?php

namespace Src\Domain\FiberCapacity\ValueObjects;

use InvalidArgumentException;

final class CapacityPercentage
{
    public const WARNING_THRESHOLD = 75.0;
    public const CRITICAL_THRESHOLD = 90.0;

    public function __construct(
        public readonly float $value
    ) {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException("Capacity percentage must be between 0 and 100");
        }
    }

    public function isWarning(): bool
    {
        return $this->value >= self::WARNING_THRESHOLD && $this->value < self::CRITICAL_THRESHOLD;
    }

    public function isCritical(): bool
    {
        return $this->value >= self::CRITICAL_THRESHOLD;
    }

    public function isOptimal(): bool
    {
        return $this->value < self::WARNING_THRESHOLD;
    }

    public function getRemainingPercentage(): float
    {
        return 100.0 - $this->value;
    }

    public function equals(CapacityPercentage $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2) . '%';
    }

    public static function fromUsedAndTotal(int $used, int $total): self
    {
        if ($total === 0) {
            return new self(0.0);
        }
        return new self(($used / $total) * 100);
    }
}
