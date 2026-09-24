<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ActivationApprovedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $approvalId,
        public readonly Uuid $inspectionId,
        public readonly Uuid $taskId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $approvalId,
        Uuid $inspectionId,
        Uuid $taskId,
    ): self {
        return new self(
            $approvalId,
            $inspectionId,
            $taskId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
