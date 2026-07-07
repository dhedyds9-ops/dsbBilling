<?php

namespace Src\Domain\Workforce\ValueObjects;

readonly class RouteLocation {
    public function __construct(
        public string $name,
        public GPSCoordinate $coordinate,
        public ?string $address = null,
    ) {}

    public function toArray(): array {
        return [
            'name' => $this->name,
            'coordinate' => $this->coordinate->toArray(),
            'address' => $this->address,
        ];
    }
}
