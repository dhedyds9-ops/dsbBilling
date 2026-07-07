<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ChecklistItem {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public string $description,
        public bool $completed = false,
        public ?string $notes = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $taskId,
        string $description,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $description,
        );
    }

    public function markComplete(?string $notes = null): void {
        $this->completed = true;
        $this->notes = $notes;
        $this->completedAt = new DateTimeImmutable();
    }
}
