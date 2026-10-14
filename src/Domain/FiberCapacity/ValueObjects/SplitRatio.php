<?php

namespace Src\Domain\FiberCapacity\ValueObjects;

use InvalidArgumentException;

final class SplitRatio
{
    public const SUPPORTED_RATIOS = [2, 4, 8, 16, 32, 64, 128];

    public function __construct(
        public readonly int $value
    ) {
        if (!in_array($value, self::SUPPORTED_RATIOS)) {
            throw new InvalidArgumentException(
                "Split ratio must be one of: " . implode(', ', self::SUPPORTED_RATIOS)
            );
        }
    }

    public function getPortCount(): int
    {
        return $this->value;
    }

    public function getLevel(): int
    {
        return (int) log2($this->value);
    }

    public function getPowerLoss(): float
    {
        return $this->getLevel() * 3.5;
    }

    public function equals(SplitRatio $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return "1:{$this->value}";
    }

    public static function fromString(string $ratio): self
    {
        $parts = explode(':', $ratio);
        $value = (int) ($parts[1] ?? $parts[0]);
        return new self($value);
    }
}
