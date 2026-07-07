<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Events\GoodsIssued;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class GoodsIssue extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $issueNumber,
        public readonly Uuid $warehouseId,
        public readonly Uuid $issuedBy,
        public readonly Uuid $issuedTo,
        public readonly string $issuedToType,
        public readonly DateTimeImmutable $issuedAt,
        public readonly string $status,
        public readonly ?string $referenceType = null,
        public readonly ?Uuid $referenceId = null,
        public readonly ?string $purpose = null,
        public readonly ?string $notes = null,
        public readonly array $items = []
    ) {}

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_CANCELLED = 'cancelled';

    public static function create(
        string $issueNumber,
        Uuid $warehouseId,
        Uuid $issuedBy,
        Uuid $issuedTo,
        string $issuedToType,
        ?string $purpose = null
    ): self {
        return new self(
            id: Uuid::generate(),
            issueNumber: $issueNumber,
            warehouseId: $warehouseId,
            issuedBy: $issuedBy,
            issuedTo: $issuedTo,
            issuedToType: $issuedToType,
            issuedAt: new DateTimeImmutable(),
            status: self::STATUS_PENDING,
            purpose: $purpose,
            notes: null,
            items: []
        );
    }

    public function addItem(Uuid $productId, int $quantity, ?Uuid $assetId = null): void
    {
        $this->items[] = [
            'product_id' => $productId->value,
            'asset_id' => $assetId?->value,
            'quantity' => $quantity,
            'issued_quantity' => 0
        ];
    }

    public function approve(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException("Issue must be pending to be approved");
        }
        $this->status = self::STATUS_APPROVED;
    }

    public function issue(): void
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \DomainException("Issue must be approved before issuing");
        }
        $this->status = self::STATUS_ISSUED;
        
        foreach ($this->items as $item) {
            $this->recordThat(new GoodsIssued(
                $item['product_id'],
                $item['quantity'],
                $this->warehouseId
            ));
        }
    }

    public function cancel(): void
    {
        if ($this->status === self::STATUS_ISSUED) {
            throw new \DomainException("Cannot cancel an already issued goods issue");
        }
        $this->status = self::STATUS_CANCELLED;
    }
}
