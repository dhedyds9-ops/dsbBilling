<?php

namespace Src\Domain\Monitoring\Events;

use Src\Domain\Monitoring\AlarmSeverity;
use Src\Domain\SharedKernel\Events\DomainEvent;

class AlarmTriggeredEvent extends DomainEvent
{
    public function __construct(
        public readonly string $alarmId,
        public readonly string $deviceId,
        public readonly string $metricName,
        public readonly AlarmSeverity $severity,
        public readonly string $message,
        public readonly float $value,
        public readonly float $threshold,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'alarm.triggered';
    }
}
