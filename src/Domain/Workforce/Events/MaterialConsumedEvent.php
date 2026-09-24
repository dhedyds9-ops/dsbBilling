<?php

namespace Src\Domain\Workforce\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class MaterialConsumedEvent implements DomainEvent {
    public function __construct(
        public readonly Uuid $consumptionId,
        public readonly Uuid $taskId,
        public readonly Uuid $inventoryItemId,
        public readonly int $quantity,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public static function create(
        Uuid $consumptionId,
        Uuid $taskId,
        Uuid $inventoryItemId,
        int $quantity,
    ): self {
        return new self(
            $consumptionId,
            $taskId,
            $inventoryItemId,
            $quantity,
            new \DateTimeImmutable(),
        );
    }

    public function occurredAt(): \DateTimeImmutable {
        return $this->occurredAt;
    }
}
