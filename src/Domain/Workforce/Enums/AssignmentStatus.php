<?php

namespace Src\Domain\Workforce\Enums;

enum AssignmentStatus: string {
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
