<?php

namespace Src\Domain\GIS\ValueObjects;

readonly class Latitude {
    public function __construct(
        public float $value,
    ) {
        if ($value < -90 || $value > 90) {
            throw new \InvalidArgumentException("Latitude must be between -90 and 90, got {$value}");
        }
    }

    public function toRadians(): float {
        return deg2rad($this->value);
    }

    public function __toString(): string {
        return (string) $this->value;
    }
}
