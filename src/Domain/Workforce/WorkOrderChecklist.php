<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkOrderChecklist {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workOrderId,
        public string $item,
        public bool $completed,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(Uuid $workOrderId, string $item): self {
        return new self(Uuid::random(), $workOrderId, $item, false);
    }

    public function markComplete(): void {
        $this->completed = true;
        $this->completedAt = new DateTimeImmutable();
    }

    public function markIncomplete(): void {
        $this->completed = false;
        $this->completedAt = null;
    }
}
