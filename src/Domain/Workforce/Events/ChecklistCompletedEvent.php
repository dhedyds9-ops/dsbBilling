<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ChecklistCompletedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $taskId,
        public readonly Uuid $checklistId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $taskId,
        Uuid $checklistId,
    ): self {
        return new self(
            $taskId,
            $checklistId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
