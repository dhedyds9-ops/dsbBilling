<?php

namespace Src\Domain\Outage\Events;

use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\Outage\Enums\OutageSeverity;
use Src\Domain\SharedKernel\Events\DomainEvent;

class OutageDetected extends DomainEvent
{
    public function __construct(
        public readonly string $outageId,
        public readonly string $nodeId,
        public readonly NodeType $nodeType,
        public readonly OutageSeverity $severity,
        public readonly string $detectionMethod,
        public readonly array $metrics = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'outage.detected';
    }

    public function getImpactDescription(): string
    {
        return "Outage detected on {$this->nodeType->label()} ({$this->nodeId}) - Severity: {$this->severity->label()}";
    }
}
