<?php

namespace Src\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

readonly class SerialNumber
{
    public function __construct(
        public string $value,
        public ?string $manufacturer = null,
        public ?\DateTimeImmutable $manufacturedDate = null
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException("Serial number cannot be empty");
        }
        if (strlen($value) < 6 || strlen($value) > 50) {
            throw new InvalidArgumentException("Serial number must be between 6 and 50 characters");
        }
    }

    public static function generate(string $prefix = 'SN'): self
    {
        $timestamp = time();
        $random = random_int(1000, 9999);
        return new self(
            sprintf("%s-%d-%04d", $prefix, $timestamp, $random)
        );
    }

    public function equals(self $other): bool
    {
        return strtoupper($this->value) === strtoupper($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
