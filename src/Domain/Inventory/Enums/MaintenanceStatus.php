<?php

namespace Src\Domain\Inventory\Enums;

enum MaintenanceStatus: string
{
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::SCHEDULED => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::OVERDUE => 'Overdue',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::SCHEDULED, self::IN_PROGRESS]);
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
