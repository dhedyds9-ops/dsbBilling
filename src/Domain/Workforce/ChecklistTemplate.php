<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\TaskType;

class ChecklistTemplate extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public readonly TaskType $taskType,
        public string $description = '',
        public bool $isActive = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        /** @var ChecklistItem[] */
        public array $items = [],
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        string $name,
        TaskType $taskType,
        string $description = '',
    ): self {
        return new self(
            Uuid::random(),
            $name,
            $taskType,
            $description,
            true,
        );
    }

    public function addItem(ChecklistItem $item): void {
        $this->items[] = $item;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeItem(Uuid $itemId): void {
        $this->items = array_filter($this->items, fn($item) => !$item->id->equals($itemId));
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateName(string $name): void {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(string $description): void {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }
}
