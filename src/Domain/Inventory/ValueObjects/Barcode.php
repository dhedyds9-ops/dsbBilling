<?php

namespace Src\Domain\Inventory\ValueObjects;

readonly class Barcode
{
    public function __construct(
        public string $value,
        public string $type = 'CODE128' // CODE128, CODE39, EAN13, QR
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException("Barcode value cannot be empty");
        }
    }

    public static function generate(int $length = 12): self
    {
        $chars = '0123456789';
        $value = '';
        for ($i = 0; $i < $length; $i++) {
            $value .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return new self($value, 'CODE128');
    }

    public static function fromSerialNumber(SerialNumber $serial): self
    {
        return new self($serial->value, 'CODE128');
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value && $this->type === $other->type;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
