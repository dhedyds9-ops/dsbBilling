<?php

namespace Src\Domain\Workflow\Enums;

enum WorkflowInstanceStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case WAITING = 'waiting';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case ESCALATED = 'escalated';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::RUNNING => 'Running',
            self::WAITING => 'Waiting for Input',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
            self::ESCALATED => 'Escalated',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::FAILED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::RUNNING, self::WAITING, self::ESCALATED]);
    }
}
