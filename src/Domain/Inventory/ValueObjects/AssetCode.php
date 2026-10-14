<?php

namespace Src\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

readonly class AssetCode
{
    public function __construct(
        public string $prefix,
        public int $sequence,
        public string $year
    ) {
        if (empty($prefix)) {
            throw new InvalidArgumentException("Prefix cannot be empty");
        }
        if ($sequence < 0) {
            throw new InvalidArgumentException("Sequence must be non-negative");
        }
    }

    public static function generate(string $prefix = 'AST'): self
    {
        return new self(
            $prefix,
            random_int(10000, 99999),
            date('Y')
        );
    }

    public static function fromString(string $code): self
    {
        // Format: PREFIX-SEQ-YYYY
        $parts = explode('-', $code);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException("Invalid asset code format: {$code}");
        }

        return new self($parts[0], (int) $parts[1], $parts[2]);
    }

    public function toString(): string
    {
        return sprintf("%s-%d-%s", $this->prefix, $this->sequence, $this->year);
    }

    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
