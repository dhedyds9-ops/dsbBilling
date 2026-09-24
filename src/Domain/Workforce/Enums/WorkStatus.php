<?php

namespace Src\Domain\Workforce\Enums;

enum WorkStatus: string {
    case TICKET = 'ticket';
    case DISPATCHED = 'dispatched';
    case ASSIGNED = 'assigned';
    case ACCEPTED = 'accepted';
    case ON_THE_WAY = 'on_the_way';
    case ARRIVED = 'arrived';
    case WORKING = 'working';
    case COMPLETED = 'completed';
    case QUALITY_CHECK = 'quality_check';
    case CLOSED = 'closed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
