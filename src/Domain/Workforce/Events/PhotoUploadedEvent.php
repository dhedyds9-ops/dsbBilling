<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PhotoUploadedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $photoId,
        public readonly Uuid $taskId,
        public readonly string $photoPath,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $photoId,
        Uuid $taskId,
        string $photoPath,
    ): self {
        return new self(
            $photoId,
            $taskId,
            $photoPath,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
