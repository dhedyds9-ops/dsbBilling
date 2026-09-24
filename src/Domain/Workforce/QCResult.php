<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\QCResultStatus;

class QCResult extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $checklistId,
        public QCResultStatus $status,
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $checklistId,
        QCResultStatus $status,
        ?string $notes = null,
    ): self {
        return new self(
            Uuid::random(),
            $checklistId,
            $status,
            $notes,
        );
    }

    public function updateStatus(QCResultStatus $status): void {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }
}
