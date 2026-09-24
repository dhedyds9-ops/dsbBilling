<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class GPSHistory extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $technicianId,
        public readonly GPSCoordinate $coordinate,
        public readonly ?float $speed = null,
        public readonly ?float $heading = null,
        public readonly ?float $altitude = null,
        public readonly DateTimeImmutable $loggedAt,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $technicianId,
        GPSCoordinate $coordinate,
        ?float $speed = null,
        ?float $heading = null,
        ?float $altitude = null,
    ): self {
        return new self(
            Uuid::random(),
            $technicianId,
            $coordinate,
            $speed,
            $heading,
            $altitude,
            new DateTimeImmutable()
        );
    }
}
