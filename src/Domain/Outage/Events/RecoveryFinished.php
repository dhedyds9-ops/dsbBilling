<?php

namespace Src\Domain\Outage\Events;

use Src\Domain\Outage\Enums\OutageStatus;
use Src\Domain\SharedKernel\Events\DomainEvent;

class RecoveryFinished extends DomainEvent
{
    public function __construct(
        public readonly string $outageId,
        public readonly string $recoveryId,
        public readonly bool $fullyRestored,
        public readonly int $actualDurationMinutes,
        public readonly int $customerRestoredCount,
        public readonly int $totalAffectedCount,
        public readonly ?string $notes = null
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'outage.recovery_finished';
    }

    public function getRestorationPercentage(): float
    {
        if ($this->totalAffectedCount === 0) {
            return 100.0;
        }
        return ($this->customerRestoredCount / $this->totalAffectedCount) * 100;
    }

    public function getDurationHours(): float
    {
        return $this->actualDurationMinutes / 60;
    }

    public function isOnTime(int $slaMinutes): bool
    {
        return $this->actualDurationMinutes <= $slaMinutes;
    }
}
