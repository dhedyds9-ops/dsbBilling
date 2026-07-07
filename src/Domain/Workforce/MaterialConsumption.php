<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class MaterialConsumption extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public readonly Uuid $inventoryItemId,
        public int $quantity,
        public string $notes = '',
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }
    }

    public static function create(
        Uuid $taskId,
        Uuid $inventoryItemId,
        int $quantity,
        string $notes = '',
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $inventoryItemId,
            $quantity,
            $notes,
        );
    }

    public function updateQuantity(int $quantity): void {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }
        $this->quantity = $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateNotes(string $notes): void {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }
}
