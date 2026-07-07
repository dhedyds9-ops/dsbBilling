<?php

namespace Src\Domain\Monitoring;

enum IncidentStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case ESCALATED = 'escalated';
}
