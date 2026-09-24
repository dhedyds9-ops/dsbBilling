<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\MovementType;
use Src\Domain\Inventory\Enums\StockStatus;
use Src\Domain\Inventory\Events\GoodsReceived;
use Src\Domain\Inventory\Events\GoodsIssued;
use Src\Domain\Inventory\Events\StockAdjusted;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class StockMovement extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $inventoryItemId,
        public readonly MovementType $movementType,
        public readonly int $quantity,
        public readonly Uuid $fromWarehouseId,
        public readonly Uuid $toWarehouseId,
        public readonly ?Uuid $fromLocationId,
        public readonly ?Uuid $toLocationId,
        public readonly ?Uuid $referenceId,
        public readonly ?string $referenceType,
        public readonly ?string $notes,
        public readonly Uuid $performedBy,
        public readonly DateTimeImmutable $performedAt
    ) {}

    public static function receive(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $toWarehouseId,
        Uuid $performedBy,
        ?Uuid $referenceId = null,
        ?string $referenceType = null,
        ?string $notes = null
    ): self {
        $movement = new self(
            id: Uuid::generate(),
            inventoryItemId: $inventoryItemId,
            movementType: MovementType::RECEIVE,
            quantity: $quantity,
            fromWarehouseId: $toWarehouseId,
            toWarehouseId: $toWarehouseId,
            fromLocationId: null,
            toLocationId: null,
            referenceId: $referenceId,
            referenceType: $referenceType,
            notes: $notes,
            performedBy: $performedBy,
            performedAt: new DateTimeImmutable()
        );
        
        $movement->recordThat(new GoodsReceived($inventoryItemId, $quantity, $toWarehouseId));
        return $movement;
    }

    public static function issue(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $fromWarehouseId,
        Uuid $performedBy,
        ?Uuid $referenceId = null,
        ?string $referenceType = null,
        ?string $notes = null
    ): self {
        $movement = new self(
            id: Uuid::generate(),
            inventoryItemId: $inventoryItemId,
            movementType: MovementType::ISSUE,
            quantity: $quantity,
            fromWarehouseId: $fromWarehouseId,
            toWarehouseId: $fromWarehouseId,
            fromLocationId: null,
            toLocationId: null,
            referenceId: $referenceId,
            referenceType: $referenceType,
            notes: $notes,
            performedBy: $performedBy,
            performedAt: new DateTimeImmutable()
        );
        
        $movement->recordThat(new GoodsIssued($inventoryItemId, $quantity, $fromWarehouseId));
        return $movement;
    }

    public static function transfer(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $fromWarehouseId,
        Uuid $toWarehouseId,
        Uuid $performedBy,
        ?Uuid $referenceId = null,
        ?string $notes = null
    ): array {
        $movements = [];
        
        $outMovement = new self(
            id: Uuid::generate(),
            inventoryItemId: $inventoryItemId,
            movementType: MovementType::TRANSFER_OUT,
            quantity: $quantity,
            fromWarehouseId: $fromWarehouseId,
            toWarehouseId: $toWarehouseId,
            fromLocationId: null,
            toLocationId: null,
            referenceId: $referenceId,
            referenceType: 'transfer',
            notes: $notes,
            performedBy: $performedBy,
            performedAt: new DateTimeImmutable()
        );
        
        $inMovement = new self(
            id: Uuid::generate(),
            inventoryItemId: $inventoryItemId,
            movementType: MovementType::TRANSFER_IN,
            quantity: $quantity,
            fromWarehouseId: $fromWarehouseId,
            toWarehouseId: $toWarehouseId,
            fromLocationId: null,
            toLocationId: null,
            referenceId: $referenceId,
            referenceType: 'transfer',
            notes: $notes,
            performedBy: $performedBy,
            performedAt: new DateTimeImmutable()
        );
        
        $outMovement->recordThat(new StockAdjusted($inventoryItemId, -$quantity, MovementType::TRANSFER_OUT->value));
        $inMovement->recordThat(new StockAdjusted($inventoryItemId, $quantity, MovementType::TRANSFER_IN->value));
        
        return [$outMovement, $inMovement];
    }

    public function isIncoming(): bool
    {
        return $this->movementType->isIncoming();
    }

    public function isOutgoing(): bool
    {
        return $this->movementType->isOutgoing();
    }

    public function getSignedQuantity(): int
    {
        return $this->isIncoming() ? $this->quantity : -$this->quantity;
    }
}
