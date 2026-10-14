<?php

namespace Src\Domain\Monitoring;

enum AlarmSeverity: string
{
    case CRITICAL = 'critical';
    case WARNING = 'warning';
    case INFO = 'info';
    case OK = 'ok';
}
