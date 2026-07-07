<?php

namespace Src\Domain\Monitoring;

enum AlarmStatus: string
{
    case OPEN = 'open';
    case ACKNOWLEDGED = 'acknowledged';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
