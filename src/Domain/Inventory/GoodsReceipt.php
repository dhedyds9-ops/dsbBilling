<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Events\GoodsReceived;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class GoodsReceipt extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $receiptNumber,
        public readonly Uuid $vendorId,
        public readonly Uuid $warehouseId,
        public readonly Uuid $receivedBy,
        public readonly DateTimeImmutable $receivedAt,
        public readonly string $status,
        public readonly ?Uuid $purchaseOrderId = null,
        public readonly ?string $notes = null,
        public readonly ?string $condition = null,
        public readonly array $items = []
    ) {}

    public const STATUS_PENDING = 'pending';
    public const STATUS_INSPECTED = 'inspected';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_REJECTED = 'rejected';

    public static function create(
        string $receiptNumber,
        Uuid $vendorId,
        Uuid $warehouseId,
        Uuid $receivedBy,
        ?Uuid $purchaseOrderId = null
    ): self {
        $receipt = new self(
            id: Uuid::generate(),
            receiptNumber: $receiptNumber,
            vendorId: $vendorId,
            warehouseId: $warehouseId,
            receivedBy: $receivedBy,
            receivedAt: new DateTimeImmutable(),
            status: self::STATUS_PENDING,
            purchaseOrderId: $purchaseOrderId,
            notes: null,
            condition: null,
            items: []
        );
        
        return $receipt;
    }

    public function addItem(Uuid $productId, int $quantity, ?string $serialNumber = null): void
    {
        $this->items[] = [
            'product_id' => $productId->value,
            'quantity' => $quantity,
            'serial_number' => $serialNumber,
            'received_quantity' => 0,
            'condition' => 'good',
            'notes' => null
        ];
    }

    public function inspect(int $itemIndex, int $receivedQuantity, string $condition, ?string $notes = null): void
    {
        if (!isset($this->items[$itemIndex])) {
            throw new \InvalidArgumentException("Item not found at index {$itemIndex}");
        }
        
        $this->items[$itemIndex]['received_quantity'] = $receivedQuantity;
        $this->items[$itemIndex]['condition'] = $condition;
        $this->items[$itemIndex]['notes'] = $notes;
    }

    public function complete(): void
    {
        $this->status = self::STATUS_COMPLETE;
        
        foreach ($this->items as $item) {
            $this->recordThat(new GoodsReceived(
                $item['product_id'],
                $item['received_quantity'],
                $this->warehouseId
            ));
        }
    }

    public function reject(string $reason): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->notes = $reason;
    }

    public function getTotalReceivedQuantity(): int
    {
        return array_sum(array_column($this->items, 'received_quantity'));
    }
}
