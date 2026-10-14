<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetInstalled implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly Uuid $installationId,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.installed';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
