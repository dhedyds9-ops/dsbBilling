<?php

namespace Src\Domain\GIS\ValueObjects;

readonly class GeoDistance {
    public function __construct(
        public float $meters,
    ) {
        if ($meters < 0) {
            throw new \InvalidArgumentException("Distance cannot be negative");
        }
    }

    public static function fromMeters(float $meters): self {
        return new self($meters);
    }

    public static function fromKilometers(float $km): self {
        return new self($km * 1000);
    }

    public static function fromMiles(float $miles): self {
        return new self($miles * 1609.344);
    }

    public function toKilometers(): float {
        return $this->meters / 1000;
    }

    public function toMiles(): float {
        return $this->meters / 1609.344;
    }

    public function __toString(): string {
        return "{$this->meters} meters";
    }
}
