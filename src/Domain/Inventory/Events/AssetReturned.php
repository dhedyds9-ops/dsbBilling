<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetReturned implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly string $previousStatus,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.returned';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
