<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\AssetStatus;
use Src\Domain\Inventory\Enums\StockStatus;
use Src\Domain\Inventory\ValueObjects\AssetCondition;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class InventoryItem extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $productId,
        public readonly Uuid $warehouseId,
        public readonly string $sku,
        public readonly string $name,
        public readonly int $quantity,
        public readonly int $reservedQuantity = 0,
        public readonly int $minStockLevel = 0,
        public readonly int $maxStockLevel = 0,
        public readonly StockStatus $status = StockStatus::AVAILABLE,
        public readonly ?Uuid $locationId = null,
        public readonly ?Uuid $rackId = null,
        public readonly ?int $shelfNumber = null,
        public readonly ?DateTimeImmutable $expiryDate = null,
        public readonly array $metadata = []
    ) {}

    public static function create(
        Uuid $productId,
        Uuid $warehouseId,
        string $sku,
        string $name,
        int $quantity = 0,
        int $minStockLevel = 0,
        int $maxStockLevel = 0
    ): self {
        return new self(
            id: Uuid::generate(),
            productId: $productId,
            warehouseId: $warehouseId,
            sku: $sku,
            name: $name,
            quantity: $quantity,
            reservedQuantity: 0,
            minStockLevel: $minStockLevel,
            maxStockLevel: $maxStockLevel,
            status: StockStatus::AVAILABLE
        );
    }

    public function receive(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be positive");
        }
        $this->quantity += $quantity;
    }

    public function issue(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be positive");
        }
        if ($quantity > $this->getAvailableQuantity()) {
            throw new \DomainException("Insufficient stock. Available: {$this->getAvailableQuantity()}");
        }
        $this->quantity -= $quantity;
    }

    public function reserve(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be positive");
        }
        if ($quantity > $this->getAvailableQuantity()) {
            throw new \DomainException("Cannot reserve more than available quantity");
        }
        $this->reservedQuantity += $quantity;
    }

    public function releaseReservation(int $quantity): void
    {
        $this->reservedQuantity = max(0, $this->reservedQuantity - $quantity);
    }

    public function adjust(int $quantity, string $reason): void
    {
        $this->quantity += $quantity;
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
    }

    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->reservedQuantity;
    }

    public function isBelowMinStock(): bool
    {
        return $this->quantity <= $this->minStockLevel;
    }

    public function isAboveMaxStock(): bool
    {
        return $this->maxStockLevel > 0 && $this->quantity >= $this->maxStockLevel;
    }

    public function needsReorder(): bool
    {
        return $this->isBelowMinStock();
    }

    public function isExpired(): bool
    {
        if ($this->expiryDate === null) {
            return false;
        }
        return $this->expiryDate < new DateTimeImmutable();
    }

    public function quarantine(): void
    {
        $this->status = StockStatus::QUARANTINE;
    }

    public function damage(): void
    {
        $this->status = StockStatus::DAMAGED;
    }

    public function markObsolete(): void
    {
        $this->status = StockStatus::OBSOLETE;
    }

    public function restore(): void
    {
        $this->status = StockStatus::AVAILABLE;
    }
}
