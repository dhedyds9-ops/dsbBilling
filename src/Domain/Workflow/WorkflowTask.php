<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\TaskStatus;
use Src\Domain\Workflow\ValueObjects\SLATimer;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class WorkflowTask extends AggregateRoot
{
    private TaskStatus $status;
    private ?Uuid $assignedTo = null;
    private ?string $assignedToType = null;
    private ?DateTimeImmutable $assignedAt = null;
    private ?DateTimeImmutable $startedAt = null;
    private ?DateTimeImmutable $completedAt = null;
    private ?DateTimeImmutable $deadline = null;
    private ?string $notes = null;
    private array $outputs = [];
    private int $retryCount = 0;
    private ?Uuid $parentTaskId = null;
    private array $subTasks = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $instanceId,
        public readonly Uuid $nodeId,
        public readonly string $name,
        public readonly string $description,
        public readonly ?SLATimer $slaTimer = null,
        public readonly int $priority = 5,
        public readonly array $metadata = []
    ) {
        $this->status = TaskStatus::PENDING;
    }

    public static function create(
        Uuid $instanceId,
        Uuid $nodeId,
        string $name,
        string $description,
        ?SLATimer $slaTimer = null,
        int $priority = 5
    ): self {
        $task = new self(
            id: Uuid::generate(),
            instanceId: $instanceId,
            nodeId: $nodeId,
            name: $name,
            description: $description,
            slaTimer: $slaTimer,
            priority: $priority
        );
        
        if ($slaTimer !== null) {
            $task->deadline = $slaTimer->calculateDeadline(new DateTimeImmutable());
        }
        
        return $task;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function assignTo(Uuid $userId, string $userType = 'user'): void
    {
        $this->assignedTo = $userId;
        $this->assignedToType = $userType;
        $this->assignedAt = new DateTimeImmutable();
        $this->status = TaskStatus::ASSIGNED;
    }

    public function start(): void
    {
        if ($this->status !== TaskStatus::ASSIGNED) {
            throw new \DomainException("Task must be assigned before starting");
        }
        $this->status = TaskStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
    }

    public function complete(array $outputs = []): void
    {
        if (!in_array($this->status, [TaskStatus::IN_PROGRESS, TaskStatus::ASSIGNED])) {
            throw new \DomainException("Task cannot be completed in current status");
        }
        $this->status = TaskStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->outputs = $outputs;
    }

    public function skip(string $reason): void
    {
        $this->status = TaskStatus::SKIPPED;
        $this->completedAt = new DateTimeImmutable();
        $this->outputs = ['skipped_reason' => $reason];
    }

    public function reject(string $reason): void
    {
        $this->status = TaskStatus::REJECTED;
        $this->completedAt = new DateTimeImmutable();
        $this->outputs = ['rejected_reason' => $reason];
    }

    public function cancel(): void
    {
        $this->status = TaskStatus::CANCELLED;
        $this->completedAt = new DateTimeImmutable();
    }

    public function retry(): void
    {
        if ($this->retryCount >= 3) {
            throw new \DomainException("Maximum retry attempts reached");
        }
        $this->retryCount++;
        $this->status = TaskStatus::ASSIGNED;
        $this->completedAt = null;
        
        if ($this->slaTimer !== null) {
            $this->deadline = $this->slaTimer->calculateDeadline(new DateTimeImmutable());
        }
    }

    public function addSubTask(WorkflowTask $subTask): void
    {
        $subTask->parentTaskId = $this->id;
        $this->subTasks[] = $subTask;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    public function isOverdue(): bool
    {
        if ($this->deadline === null) {
            return false;
        }
        return new \DateTimeImmutable() > $this->deadline && !$this->status->isTerminal();
    }

    public function isDueSoon(int $hours = 24): bool
    {
        if ($this->deadline === null) {
            return false;
        }
        $soon = (new \DateTimeImmutable())->modify("+{$hours} hours");
        return $this->deadline <= $soon && !$this->status->isTerminal();
    }

    public function getRemainingSLAMinutes(): ?int
    {
        if ($this->deadline === null) {
            return null;
        }
        $now = new \DateTimeImmutable();
        if ($now > $this->deadline) {
            return 0;
        }
        return $now->diff($this->deadline)->i;
    }

    public function escalate(): void
    {
        // Mark as escalated but keep in current status
        $this->metadata['escalated_at'] = (new DateTimeImmutable())->format('c');
    }
}
