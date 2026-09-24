<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Events\StockAdjusted;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class StockAdjustment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $adjustmentNumber,
        public readonly Uuid $warehouseId,
        public readonly Uuid $adjustedBy,
        public readonly DateTimeImmutable $adjustedAt,
        public readonly string $reason,
        public readonly string $status,
        public readonly ?string $notes = null,
        public readonly ?Uuid $approvedBy = null,
        public readonly ?DateTimeImmutable $approvedAt = null,
        public readonly array $items = []
    ) {}

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REASON_COUNT = 'count';
    public const REASON_DAMAGE = 'damage';
    public const REASON_EXPIRY = 'expiry';
    public const REASON_LOSS = 'loss';
    public const REASON_THEFT = 'theft';
    public const REASON_FOUND = 'found';
    public const REASON_RETURN = 'return';
    public const REASON_OTHER = 'other';

    public static function create(
        string $adjustmentNumber,
        Uuid $warehouseId,
        Uuid $adjustedBy,
        string $reason,
        ?string $notes = null
    ): self {
        return new self(
            id: Uuid::generate(),
            adjustmentNumber: $adjustmentNumber,
            warehouseId: $warehouseId,
            adjustedBy: $adjustedBy,
            adjustedAt: new DateTimeImmutable(),
            reason: $reason,
            status: self::STATUS_DRAFT,
            notes: $notes,
            items: []
        );
    }

    public function addItem(
        Uuid $inventoryItemId,
        int $currentQuantity,
        int $newQuantity,
        ?string $notes = null
    ): void {
        $adjustmentQuantity = $newQuantity - $currentQuantity;
        
        $this->items[] = [
            'inventory_item_id' => $inventoryItemId->value,
            'current_quantity' => $currentQuantity,
            'new_quantity' => $newQuantity,
            'adjustment_quantity' => $adjustmentQuantity,
            'notes' => $notes
        ];
    }

    public function submit(): void
    {
        if (empty($this->items)) {
            throw new \DomainException("Cannot submit adjustment without items");
        }
        $this->status = self::STATUS_PENDING;
    }

    public function approve(Uuid $approvedBy): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException("Adjustment must be pending to be approved");
        }
        
        $this->status = self::STATUS_APPROVED;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new DateTimeImmutable();
        
        foreach ($this->items as $item) {
            $this->recordThat(new StockAdjusted(
                $item['inventory_item_id'],
                $item['adjustment_quantity'],
                $this->reason
            ));
        }
    }

    public function complete(): void
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \DomainException("Adjustment must be approved to be completed");
        }
        $this->status = self::STATUS_COMPLETED;
    }

    public function cancel(): void
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \DomainException("Cannot cancel a completed adjustment");
        }
        $this->status = self::STATUS_CANCELLED;
    }

    public function getTotalAdjustment(): int
    {
        return array_sum(array_column($this->items, 'adjustment_quantity'));
    }
}
