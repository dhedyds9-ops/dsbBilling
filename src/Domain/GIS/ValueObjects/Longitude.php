<?php

namespace Src\Domain\GIS\ValueObjects;

readonly class Longitude {
    public function __construct(
        public float $value,
    ) {
        if ($value < -180 || $value > 180) {
            throw new \InvalidArgumentException("Longitude must be between -180 and 180, got {$value}");
        }
    }

    public function toRadians(): float {
        return deg2rad($this->value);
    }

    public function __toString(): string {
        return (string) $this->value;
    }
}
