<?php

namespace Src\Domain\Scheduler;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ScheduledTaskStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}

class ScheduledTask extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public string $task,
        public string $cronExpression,
        public ScheduledTaskStatus $status = ScheduledTaskStatus::ACTIVE,
        public ?DateTimeImmutable $lastRunAt = null,
        public ?DateTimeImmutable $nextRunAt = null,
        public array $parameters = []
    ) {}

    public static function create(
        Uuid $id,
        string $name,
        string $task,
        string $cronExpression,
        array $parameters = []
    ): self {
        return new self($id, $name, $task, $cronExpression, ScheduledTaskStatus::ACTIVE, null, null, $parameters);
    }

    public function markAsRun(DateTimeImmutable $lastRunAt, DateTimeImmutable $nextRunAt): void
    {
        $this->lastRunAt = $lastRunAt;
        $this->nextRunAt = $nextRunAt;
    }

    public function pause(): void
    {
        $this->status = ScheduledTaskStatus::PAUSED;
    }

    public function resume(): void
    {
        $this->status = ScheduledTaskStatus::ACTIVE;
    }
}
