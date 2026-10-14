<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetTransferred implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly Uuid $fromWarehouseId,
        public readonly Uuid $toWarehouseId,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.transferred';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
