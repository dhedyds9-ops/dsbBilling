<?php

namespace Src\Domain\Inventory\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WarrantyExpired implements DomainEvent
{
    public function __construct(
        public readonly Uuid $assetId,
        public readonly Uuid $warrantyId,
        public readonly \DateTimeImmutable $expiredAt = null
    ) {
        $this->expiredAt = $expiredAt ?? new DateTimeImmutable();
    }

    public function getEventType(): string
    {
        return 'warranty.expired';
    }

    public function getAggregateId(): Uuid
    {
        return $this->assetId;
    }
}
