<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum WorkOrderStatus: string {
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

class WorkOrder extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly string $type,
        public readonly Uuid $ticketId,
        public readonly Uuid $customerId,
        public readonly ?Uuid $assignedTo,
        public WorkOrderStatus $status,
        public string $title,
        public string $description,
        public ?DateTimeImmutable $scheduledAt,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $completedAt,
        public ?string $priority,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        string $type,
        Uuid $ticketId,
        Uuid $customerId,
        string $title,
        string $description,
        ?DateTimeImmutable $scheduledAt = null,
        ?string $priority = 'medium',
    ): self {
        return new self(
            Uuid::random(),
            $type,
            $ticketId,
            $customerId,
            null,
            WorkOrderStatus::PENDING,
            $title,
            $description,
            $scheduledAt,
            null,
            null,
            $priority
        );
    }

    public function assign(Uuid $technicianId): void {
        $this->assignedTo = $technicianId;
        $this->status = WorkOrderStatus::ASSIGNED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function start(): void {
        $this->status = WorkOrderStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void {
        $this->status = WorkOrderStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
}
