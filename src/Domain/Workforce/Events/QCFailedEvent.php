<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class QCFailedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $inspectionId,
        public readonly Uuid $taskId,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $inspectionId,
        Uuid $taskId,
        ?string $notes = null,
    ): self {
        return new self(
            $inspectionId,
            $taskId,
            $notes,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
