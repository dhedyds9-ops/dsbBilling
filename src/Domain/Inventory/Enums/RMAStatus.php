<?php

namespace Src\Domain\Inventory\Enums;

enum RMAStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SHIPPED = 'shipped';
    case RECEIVED = 'received';
    case INSPECTING = 'inspecting';
    case REPAIRING = 'repairing';
    case REPLACING = 'replacing';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match($this) {
            self::REQUESTED => 'Requested',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::SHIPPED => 'Shipped to Vendor',
            self::RECEIVED => 'Received',
            self::INSPECTING => 'Inspecting',
            self::REPAIRING => 'Repairing',
            self::REPLACING => 'Replacing',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
            self::RETURNED => 'Returned to Customer',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::REQUESTED, self::APPROVED]);
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::RESOLVED, self::CLOSED, self::RETURNED]);
    }
}
