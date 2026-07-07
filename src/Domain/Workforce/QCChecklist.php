<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class QCChecklist extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $inspectionId,
        public readonly string $item,
        public bool $isRequired,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $inspectionId,
        string $item,
        bool $isRequired = true,
    ): self {
        return new self(
            Uuid::random(),
            $inspectionId,
            $item,
            $isRequired,
        );
    }

    public function updateItem(string $item): void {
        $this->item = $item;
        $this->updatedAt = new DateTimeImmutable();
    }
}
