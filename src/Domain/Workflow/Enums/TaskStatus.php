<?php

namespace Src\Domain\Workflow\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::SKIPPED => 'Skipped',
            self::CANCELLED => 'Cancelled',
            self::REJECTED => 'Rejected',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::SKIPPED, self::CANCELLED, self::REJECTED]);
    }

    public function requiresAction(): bool
    {
        return in_array($this, [self::ASSIGNED, self::IN_PROGRESS]);
    }
}
