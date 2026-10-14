<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class VendorPurchase extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $vendorId,
        public readonly string $purchaseNumber,
        public readonly DateTimeImmutable $purchaseDate,
        public readonly float $totalAmount,
        public readonly string $currency = 'IDR',
        public readonly string $status,
        public readonly ?Uuid $goodsReceiptId = null,
        public readonly ?string $notes = null,
        public readonly ?string $paymentTerms = null,
        public readonly ?DateTimeImmutable $expectedDelivery = null
    ) {}

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public static function create(
        Uuid $vendorId,
        string $purchaseNumber,
        DateTimeImmutable $purchaseDate,
        float $totalAmount,
        ?string $notes = null,
        ?DateTimeImmutable $expectedDelivery = null
    ): self {
        return new self(
            id: Uuid::generate(),
            vendorId: $vendorId,
            purchaseNumber: $purchaseNumber,
            purchaseDate: $purchaseDate,
            totalAmount: $totalAmount,
            status: self::STATUS_DRAFT,
            notes: $notes,
            expectedDelivery: $expectedDelivery
        );
    }

    public function approve(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException("Purchase must be pending to be approved");
        }
        $this->status = self::STATUS_APPROVED;
    }

    public function order(): void
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \DomainException("Purchase must be approved to be ordered");
        }
        $this->status = self::STATUS_ORDERED;
    }

    public function receive(Uuid $goodsReceiptId): void
    {
        $this->status = self::STATUS_RECEIVED;
        $this->goodsReceiptId = $goodsReceiptId;
    }

    public function cancel(): void
    {
        if (in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CANCELLED])) {
            throw new \DomainException("Cannot cancel a completed or already cancelled purchase");
        }
        $this->status = self::STATUS_CANCELLED;
    }
}
