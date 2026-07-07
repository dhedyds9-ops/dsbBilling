<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetTransfer extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly Uuid $fromWarehouseId,
        public readonly Uuid $toWarehouseId,
        public readonly Uuid $initiatedBy,
        public readonly DateTimeImmutable $initiatedAt,
        public readonly string $status,
        public readonly ?Uuid $approvedBy = null,
        public readonly ?DateTimeImmutable $approvedAt = null,
        public readonly ?Uuid $receivedBy = null,
        public readonly ?DateTimeImmutable $receivedAt = null,
        public readonly ?string $notes = null,
        public readonly ?string $reason = null
    ) {}

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public static function create(
        Uuid $assetId,
        Uuid $fromWarehouseId,
        Uuid $toWarehouseId,
        Uuid $initiatedBy,
        ?string $reason = null
    ): self {
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            fromWarehouseId: $fromWarehouseId,
            toWarehouseId: $toWarehouseId,
            initiatedBy: $initiatedBy,
            initiatedAt: new DateTimeImmutable(),
            status: self::STATUS_PENDING,
            notes: null,
            reason: $reason
        );
    }

    public function approve(Uuid $approvedBy): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException("Transfer must be pending to be approved");
        }
        
        $this->status = self::STATUS_APPROVED;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new DateTimeImmutable();
    }

    public function ship(): void
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \DomainException("Transfer must be approved before shipping");
        }
        
        $this->status = self::STATUS_SHIPPED;
    }

    public function receive(Uuid $receivedBy): void
    {
        if ($this->status !== self::STATUS_SHIPPED) {
            throw new \DomainException("Transfer must be shipped before receiving");
        }
        
        $this->status = self::STATUS_RECEIVED;
        $this->receivedBy = $receivedBy;
        $this->receivedAt = new DateTimeImmutable();
    }

    public function cancel(?Uuid $cancelledBy, string $reason): void
    {
        if (in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CANCELLED])) {
            throw new \DomainException("Cannot cancel a completed or already cancelled transfer");
        }
        
        $this->status = self::STATUS_CANCELLED;
        $this->notes = $reason;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isComplete(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }
}
