<?php

namespace Src\Domain\GIS\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageAreaCreated implements DomainEvent {
    public function __construct(
        public readonly Uuid $coverageAreaId,
        public readonly Uuid $parentId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $coverageAreaId,
        Uuid $parentId,
    ): self {
        return new self(
            $coverageAreaId,
            $parentId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
