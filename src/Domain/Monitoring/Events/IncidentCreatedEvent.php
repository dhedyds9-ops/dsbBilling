<?php

namespace Src\Domain\Monitoring\Events;

use Src\Domain\Monitoring\AlarmSeverity;
use Src\Domain\SharedKernel\Events\DomainEvent;

class IncidentCreatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $incidentId,
        public readonly string $rootAlarmId,
        public readonly array $relatedAlarmIds,
        public readonly string $category,
        public readonly AlarmSeverity $severity,
        public readonly string $description,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'incident.created';
    }
}
