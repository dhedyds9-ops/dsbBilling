<?php

namespace Src\Domain\Outage\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class RecoveryStarted extends DomainEvent
{
    public function __construct(
        public readonly string $outageId,
        public readonly string $recoveryId,
        public readonly string $technicianId,
        public readonly ?string $technicianName,
        public readonly int $estimatedMinutes,
        public readonly string $recoveryMethod
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'outage.recovery_started';
    }

    public function getEstimatedHours(): float
    {
        return $this->estimatedMinutes / 60;
    }
}
