<?php

namespace Src\Domain\Outage\Events;

use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\Events\DomainEvent;

class CustomerAffected extends DomainEvent
{
    public function __construct(
        public readonly string $outageId,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly ImpactLevel $impactLevel,
        public readonly array $affectedServices = [],
        public readonly int $serviceDownDuration = 0
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'outage.customer_affected';
    }

    public function isTotalImpact(): bool
    {
        return $this->impactLevel === ImpactLevel::TOTAL;
    }

    public function getAffectedServiceCount(): int
    {
        return count($this->affectedServices);
    }
}
