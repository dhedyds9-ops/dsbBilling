<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetRepaired implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly ?float $repairCost = null,
        public readonly \DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'asset.repaired';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
