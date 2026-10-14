<?php

namespace Src\Domain\GIS\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageAreaUpdated implements DomainEvent {
    public function __construct(
        public readonly Uuid $coverageAreaId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $coverageAreaId,
    ): self {
        return new self(
            $coverageAreaId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
