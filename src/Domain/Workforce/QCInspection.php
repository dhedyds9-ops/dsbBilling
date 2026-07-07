<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\QCStatus;

class QCInspection extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public readonly Uuid $inspectorId,
        public QCStatus $status,
        public ?string $notes = null,
        public ?DateTimeImmutable $inspectedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $taskId,
        Uuid $inspectorId,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $inspectorId,
            QCStatus::PENDING,
        );
    }

    public function start(): void {
        $this->status = QCStatus::IN_PROGRESS;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function pass(): void {
        $this->status = QCStatus::PASSED;
        $this->inspectedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function fail(): void {
        $this->status = QCStatus::FAILED;
        $this->inspectedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }
}
