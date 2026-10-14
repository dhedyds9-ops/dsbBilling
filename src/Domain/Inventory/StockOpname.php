<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class StockOpname extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $opnameNumber,
        public readonly Uuid $warehouseId,
        public readonly Uuid $conductedBy,
        public readonly DateTimeImmutable $scheduledAt,
        public readonly ?DateTimeImmutable $startedAt = null,
        public readonly ?DateTimeImmutable $completedAt = null,
        public readonly string $status,
        public readonly ?string $notes = null,
        public readonly array $items = []
    ) {}

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DISCREPANCY_REVIEW = 'discrepancy_review';

    public static function schedule(
        string $opnameNumber,
        Uuid $warehouseId,
        Uuid $conductedBy,
        DateTimeImmutable $scheduledAt
    ): self {
        return new self(
            id: Uuid::generate(),
            opnameNumber: $opnameNumber,
            warehouseId: $warehouseId,
            conductedBy: $conductedBy,
            scheduledAt: $scheduledAt,
            status: self::STATUS_PENDING
        );
    }

    public function start(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException("Opname must be pending to start");
        }
        $this->status = self::STATUS_IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
    }

    public function recordCount(
        Uuid $inventoryItemId,
        int $systemQuantity,
        int $countedQuantity,
        ?string $notes = null
    ): void {
        $variance = $countedQuantity - $systemQuantity;
        
        $this->items[] = [
            'inventory_item_id' => $inventoryItemId->value,
            'system_quantity' => $systemQuantity,
            'counted_quantity' => $countedQuantity,
            'variance' => $variance,
            'notes' => $notes,
            'counted_at' => new DateTimeImmutable()->format('Y-m-d H:i:s')
        ];
    }

    public function complete(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = new DateTimeImmutable();
    }

    public function requestDiscrepancyReview(): void
    {
        $hasDiscrepancy = false;
        foreach ($this->items as $item) {
            if ($item['variance'] !== 0) {
                $hasDiscrepancy = true;
                break;
            }
        }
        
        if (!$hasDiscrepancy) {
            $this->complete();
        } else {
            $this->status = self::STATUS_DISCREPANCY_REVIEW;
        }
    }

    public function cancel(): void
    {
        if ($this->status === self::STATUS_COMPLETED) {
            throw new \DomainException("Cannot cancel a completed opname");
        }
        $this->status = self::STATUS_CANCELLED;
    }

    public function getTotalVariance(): int
    {
        return array_sum(array_column($this->items, 'variance'));
    }

    public function getItemsWithDiscrepancy(): array
    {
        return array_filter($this->items, fn($item) => $item['variance'] !== 0);
    }
}
