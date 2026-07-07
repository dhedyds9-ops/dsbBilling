<?php

namespace Src\Domain\FiberCapacity\ValueObjects;

use InvalidArgumentException;

final class CoreNumber
{
    public function __construct(
        public readonly int $value
    ) {
        if ($value < 1 || $value > 144) {
            throw new InvalidArgumentException("Core number must be between 1 and 144");
        }
    }

    public function equals(CoreNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function isEven(): bool
    {
        return $this->value % 2 === 0;
    }

    public function isOdd(): bool
    {
        return $this->value % 2 !== 0;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
