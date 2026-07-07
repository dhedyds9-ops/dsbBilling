<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GoodsReceived implements DomainEvent
{
    public function __construct(
        public readonly Uuid $inventoryItemId,
        public readonly int $quantity,
        public readonly Uuid $warehouseId,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'goods.received';
    }

    public function getAggregateId(): Uuid
    {
        return $this->inventoryItemId;
    }
}
