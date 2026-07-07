<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class QualityControlPassedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $qcId,
        public readonly string $installationId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'qc.passed';
    }
}
