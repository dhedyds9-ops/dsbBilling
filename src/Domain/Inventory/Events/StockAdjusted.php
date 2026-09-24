<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class StockAdjusted implements DomainEvent
{
    public function __construct(
        public readonly Uuid $inventoryItemId,
        public readonly int $adjustmentQuantity,
        public readonly string $reason,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'stock.adjusted';
    }

    public function getAggregateId(): Uuid
    {
        return $this->inventoryItemId;
    }
}
