<?php

namespace Src\Domain\FiberCapacity\ValueObjects;

final class AvailableCore
{
    public function __construct(
        public readonly int $count,
        public readonly array $coreNumbers = []
    ) {}

    public function hasCapacity(int $required = 1): bool
    {
        return $this->count >= $required;
    }

    public function getPercentage(int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }
        return ($this->count / $total) * 100;
    }

    public function equals(AvailableCore $other): bool
    {
        return $this->count === $other->count;
    }

    public function __toString(): string
    {
        return "{$this->count} core(s) tersedia";
    }

    public static function fromUsedAndTotal(int $used, int $total): self
    {
        $available = max(0, $total - $used);
        return new self($available);
    }
}
