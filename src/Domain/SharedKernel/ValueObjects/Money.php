<?php

namespace Src\Domain\SharedKernel\ValueObjects;

use InvalidArgumentException;

readonly class Money
{
    public function __construct(
        public float $amount,
        public string $currency = 'IDR'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException("Amount cannot be negative");
        }
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currencies must match");
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currencies must match");
        }
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return "{$this->currency} {$this->amount}";
    }
}
