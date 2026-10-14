<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetCreated implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly string $assetCode,
        public readonly \DateTimeImmutable $occurredAt
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.created';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
