<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GPSLog {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $userId,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $accuracy,
        public readonly DateTimeImmutable $loggedAt,
    ) {}

    public static function create(
        Uuid $userId,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
    ): self {
        return new self(
            Uuid::random(),
            $userId,
            $latitude,
            $longitude,
            $accuracy,
            new DateTimeImmutable()
        );
    }
}
