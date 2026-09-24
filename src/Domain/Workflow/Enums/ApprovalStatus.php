<?php

namespace Src\Domain\Workflow\Enums;

enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SKIPPED = 'skipped';
    case ESCALATED = 'escalated';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::SKIPPED => 'Skipped',
            self::ESCALATED => 'Escalated',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::SKIPPED]);
    }
}
