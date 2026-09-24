<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class DigitalSignedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $signatureId,
        public readonly Uuid $taskId,
        public readonly Uuid $signerId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $signatureId,
        Uuid $taskId,
        Uuid $signerId,
    ): self {
        return new self(
            $signatureId,
            $taskId,
            $signerId,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
