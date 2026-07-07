<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\AssignmentStatus;
use Src\Domain\Workforce\ValueObjects\AssignmentPriority;

class WorkAssignment extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workOrderId,
        public readonly Uuid $technicianId,
        public readonly ?Uuid $dispatcherId = null,
        public AssignmentStatus $status = AssignmentStatus::PENDING,
        public AssignmentPriority $priority = AssignmentPriority::MEDIUM,
        public ?string $notes = null,
        public ?DateTimeImmutable $assignedAt = null,
        public ?DateTimeImmutable $acceptedAt = null,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $workOrderId,
        Uuid $technicianId,
        ?Uuid $dispatcherId = null,
        ?AssignmentPriority $priority = null,
        ?string $notes = null,
    ): self {
        return new self(
            Uuid::random(),
            $workOrderId,
            $technicianId,
            $dispatcherId,
            AssignmentStatus::PENDING,
            $priority ?? AssignmentPriority::MEDIUM,
            $notes,
        );
    }

    public function accept(): void {
        $this->status = AssignmentStatus::ACCEPTED;
        $this->acceptedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(): void {
        $this->status = AssignmentStatus::REJECTED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void {
        $this->status = AssignmentStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void {
        $this->status = AssignmentStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();
    }
}
