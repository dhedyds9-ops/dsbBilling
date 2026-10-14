<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\Outage\Enums\OutageSeverity;
use Src\Domain\Outage\Enums\OutageStatus;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\Outage\Events\OutageDetected;
use Src\Domain\Outage\Events\RecoveryStarted;
use Src\Domain\Outage\Events\RecoveryFinished;
use Src\Domain\Outage\ValueObjects\AffectedArea;
use Src\Domain\Outage\ValueObjects\ImpactAssessment;
use Src\Domain\Outage\ValueObjects\RecoveryTimeEstimate;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class Outage extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly NodeType $originNodeType,
        public readonly string $originNodeId,
        public readonly string $originNodeName,
        public OutageStatus $status,
        public OutageSeverity $severity,
        public DateTimeImmutable $detectedAt,
        public ?DateTimeImmutable $confirmedAt = null,
        public ?DateTimeImmutable $resolvedAt = null,
        public ?Uuid $incidentId = null,
        public ?string $incidentNumber = null,
        public ?string $title = null,
        public ?string $description = null,
        public int $affectedCustomerCount = 0,
        public int $affectedNodeCount = 0,
        public ImpactLevel $impactLevel = ImpactLevel::TOTAL,
        public ?AffectedArea $affectedArea = null,
        public ?ImpactAssessment $impactAssessment = null,
        public ?RecoveryTimeEstimate $recoveryEstimate = null,
        public array $timeline = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        NodeType $originNodeType,
        string $originNodeId,
        string $originNodeName,
        OutageSeverity $severity,
        string $detectionMethod
    ): self {
        $outage = new self(
            $id,
            $originNodeType,
            $originNodeId,
            $originNodeName,
            OutageStatus::DETECTED,
            $severity,
            new DateTimeImmutable()
        );

        $outage->title = "Outage on {$originNodeType->label()}: {$originNodeName}";
        $outage->timeline[] = [
            'event' => 'detected',
            'timestamp' => new DateTimeImmutable(),
            'method' => $detectionMethod,
            'severity' => $severity->value
        ];

        $outage->recordThat(new OutageDetected(
            $id->value,
            $originNodeId,
            $originNodeType,
            $severity,
            $detectionMethod
        ));

        return $outage;
    }

    public function confirm(ImpactAssessment $assessment, AffectedArea $affectedArea): void
    {
        if ($this->status !== OutageStatus::DETECTED && $this->status !== OutageStatus::ANALYZING) {
            throw new \InvalidArgumentException("Outage cannot be confirmed in current status");
        }

        $this->status = OutageStatus::CONFIRMED;
        $this->confirmedAt = new DateTimeImmutable();
        $this->impactAssessment = $assessment;
        $this->affectedArea = $affectedArea;
        $this->affectedCustomerCount = $assessment->totalAffected;
        $this->affectedNodeCount = $affectedArea->totalNodes;
        $this->impactLevel = $assessment->overallLevel;

        $this->timeline[] = [
            'event' => 'confirmed',
            'timestamp' => new DateTimeImmutable(),
            'affected_customers' => $this->affectedCustomerCount,
            'affected_nodes' => $this->affectedNodeCount
        ];
    }

    public function setIncident(Uuid $incidentId, string $incidentNumber): void
    {
        $this->incidentId = $incidentId;
        $this->incidentNumber = $incidentNumber;

        $this->timeline[] = [
            'event' => 'incident_created',
            'timestamp' => new DateTimeImmutable(),
            'incident_id' => $incidentId->value,
            'incident_number' => $incidentNumber
        ];
    }

    public function startRecovery(RecoveryTimeEstimate $estimate, string $technicianId, ?string $technicianName = null): void
    {
        if (!$this->status->isActive()) {
            throw new \InvalidArgumentException("Cannot start recovery on resolved outage");
        }

        $this->status = OutageStatus::RECOVERY_IN_PROGRESS;

        $this->recoveryEstimate = $estimate;
        $this->timeline[] = [
            'event' => 'recovery_started',
            'timestamp' => new DateTimeImmutable(),
            'technician_id' => $technicianId,
            'estimated_minutes' => $estimate->averageMinutes
        ];

        $this->recordThat(new RecoveryStarted(
            $this->id->value,
            Uuid::generate()->value,
            $technicianId,
            $technicianName,
            $estimate->averageMinutes,
            $estimate->method
        ));
    }

    public function resolve(bool $fullyRestored, int $customerRestoredCount, ?string $notes = null): void
    {
        if ($this->status !== OutageStatus::RECOVERY_IN_PROGRESS) {
            throw new \InvalidArgumentException("Cannot resolve outage that is not in recovery");
        }

        $this->resolvedAt = new DateTimeImmutable();
        $this->status = $fullyRestored ? OutageStatus::RESOLVED : OutageStatus::PARTIALLY_RESOLVED;

        $actualDuration = (int) (($this->resolvedAt->getTimestamp() - $this->detectedAt->getTimestamp()) / 60);

        $this->timeline[] = [
            'event' => 'resolved',
            'timestamp' => $this->resolvedAt,
            'fully_restored' => $fullyRestored,
            'duration_minutes' => $actualDuration,
            'customers_restored' => $customerRestoredCount,
            'notes' => $notes
        ];

        $this->recordThat(new RecoveryFinished(
            $this->id->value,
            Uuid::generate()->value,
            $fullyRestored,
            $actualDuration,
            $customerRestoredCount,
            $this->affectedCustomerCount,
            $notes
        ));
    }

    public function getDurationMinutes(): int
    {
        $end = $this->resolvedAt ?? new DateTimeImmutable();
        return (int) (($end->getTimestamp() - $this->detectedAt->getTimestamp()) / 60);
    }

    public function isWithinSla(): bool
    {
        $duration = $this->getDurationMinutes();
        $slaMinutes = $this->severity->slaDeadline() * 60;
        return $duration <= $slaMinutes;
    }

    public function getSlaRemainingMinutes(): int
    {
        $slaMinutes = $this->severity->slaDeadline() * 60;
        $duration = $this->getDurationMinutes();
        return max(0, $slaMinutes - $duration);
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
