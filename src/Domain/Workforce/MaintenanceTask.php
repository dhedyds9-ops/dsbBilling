<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\TaskStatus;
use Src\Domain\Workforce\Enums\TaskType;

class MaintenanceTask extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workOrderId,
        public readonly Uuid $assignmentId,
        public readonly Uuid $customerId,
        public readonly Uuid $serviceId,
        public TaskStatus $status = TaskStatus::NOT_STARTED,
        public readonly TaskType $type = TaskType::MAINTENANCE,
        public ?string $notes = null,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $qcAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
        Uuid $serviceId,
        ?string $notes = null,
    ): self {
        return new self(
            Uuid::random(),
            $workOrderId,
            $assignmentId,
            $customerId,
            $serviceId,
            TaskStatus::NOT_STARTED,
            TaskType::MAINTENANCE,
            $notes,
        );
    }

    public function startTask(): void {
        $this->status = TaskStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function moveToChecklist(): void {
        $this->status = TaskStatus::CHECKLIST_IN_PROGRESS;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function moveToPhoto(): void {
        $this->status = TaskStatus::PHOTO_IN_PROGRESS;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function moveToMaterial(): void {
        $this->status = TaskStatus::MATERIAL_CONSUMPTION;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function moveToSignature(): void {
        $this->status = TaskStatus::SIGNATURE_PENDING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function moveToQC(): void {
        $this->status = TaskStatus::QC_PENDING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function approveQC(): void {
        $this->status = TaskStatus::QC_APPROVED;
        $this->qcAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function rejectQC(): void {
        $this->status = TaskStatus::QC_REJECTED;
        $this->qcAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function completeTask(): void {
        $this->status = TaskStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function closeTask(): void {
        $this->status = TaskStatus::CLOSED;
        $this->updatedAt = new DateTimeImmutable();
    }
}
