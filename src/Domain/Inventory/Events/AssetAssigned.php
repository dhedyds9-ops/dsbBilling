<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetAssigned implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly Uuid $assignedToId,
        public readonly string $assignedToType,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.assigned';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
