<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\QCApprovalStatus;

class QCApproval extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $inspectionId,
        public readonly Uuid $approverId,
        public QCApprovalStatus $status,
        public ?string $notes = null,
        public ?DateTimeImmutable $approvedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $inspectionId,
        Uuid $approverId,
    ): self {
        return new self(
            Uuid::random(),
            $inspectionId,
            $approverId,
            QCApprovalStatus::PENDING,
        );
    }

    public function approve(): void {
        $this->status = QCApprovalStatus::APPROVED;
        $this->approvedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(): void {
        $this->status = QCApprovalStatus::REJECTED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }
}
