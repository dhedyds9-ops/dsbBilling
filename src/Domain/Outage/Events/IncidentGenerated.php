<?php

namespace Src\Domain\Outage\Events;

use Src\Domain\Outage\Enums\OutageSeverity;
use Src\Domain\SharedKernel\Events\DomainEvent;

class IncidentGenerated extends DomainEvent
{
    public function __construct(
        public readonly string $outageId,
        public readonly string $incidentId,
        public readonly string $incidentNumber,
        public readonly OutageSeverity $severity,
        public readonly string $title,
        public readonly string $description,
        public readonly int $affectedCustomerCount,
        public readonly array $affectedNodes = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'outage.incident_generated';
    }

    public function getPriorityScore(): int
    {
        return $this->severity->priority() * 100 + min(100, $this->affectedCustomerCount);
    }
}
