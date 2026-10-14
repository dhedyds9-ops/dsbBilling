<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RootCause extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $outageId,
        public readonly NodeType $affectedNodeType,
        public readonly string $affectedNodeId,
        public readonly string $affectedNodeName,
        public readonly string $causeCode,
        public readonly string $causeDescription,
        public readonly string $rootCause,
        public readonly string $impactDescription,
        public readonly ?DateTimeImmutable $occurredAt = null,
        public ?DateTimeImmutable $identifiedAt = null,
        public ?string $identifiedBy = null,
        public int $confidenceScore = 0,
        public array $supportingEvidence = [],
        public array $relatedTickets = [],
        public array $similarCases = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $outageId,
        NodeType $affectedNodeType,
        string $affectedNodeId,
        string $affectedNodeName,
        string $causeCode,
        string $causeDescription,
        string $rootCause,
        string $impactDescription,
        ?DateTimeImmutable $occurredAt = null
    ): self {
        return new self(
            $id,
            $outageId,
            $affectedNodeType,
            $affectedNodeId,
            $affectedNodeName,
            $causeCode,
            $causeDescription,
            $rootCause,
            $impactDescription,
            $occurredAt ?? new DateTimeImmutable()
        );
    }

    public function identify(string $identifiedBy, int $confidenceScore = 100): void
    {
        $this->identifiedAt = new DateTimeImmutable();
        $this->identifiedBy = $identifiedBy;
        $this->confidenceScore = $confidenceScore;

        $this->metadata['identified_at'] = $this->identifiedAt;
        $this->metadata['confidence_score'] = $confidenceScore;
    }

    public function addEvidence(string $evidenceType, string $description, array $details = []): void
    {
        $this->supportingEvidence[] = [
            'type' => $evidenceType,
            'description' => $description,
            'details' => $details,
            'added_at' => new DateTimeImmutable()
        ];
    }

    public function linkTicket(string $ticketId, string $ticketNumber): void
    {
        $this->relatedTickets[] = [
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber,
            'linked_at' => new DateTimeImmutable()
        ];
    }

    public function addSimilarCase(string $caseId, string $caseDescription, string $resolution): void
    {
        $this->similarCases[] = [
            'case_id' => $caseId,
            'description' => $caseDescription,
            'resolution' => $resolution
        ];
    }

    public function isIdentified(): bool
    {
        return $this->identifiedAt !== null;
    }

    public function getConfidenceLevel(): string
    {
        return match(true) {
            $this->confidenceScore >= 90 => 'high',
            $this->confidenceScore >= 70 => 'medium',
            default => 'low',
        };
    }

    public function getDurationMinutes(): ?int
    {
        if ($this->occurredAt === null) {
            return null;
        }

        $end = $this->identifiedAt ?? new DateTimeImmutable();
        return (int) (($end->getTimestamp() - $this->occurredAt->getTimestamp()) / 60);
    }

    public function getRecommendedAction(): string
    {
        return match($this->causeCode) {
            'POWER_FAILURE' => 'Check power supply and restore UPS/generator',
            'FIBER_CUT' => 'Locate cut point and splice fiber',
            'HARDWARE_FAILURE' => 'Replace faulty hardware component',
            'CONFIGURATION_ERROR' => 'Review and correct configuration',
            'SOFTWARE_BUG' => 'Apply software patch or rollback',
            'OVERLOAD' => 'Balance load or upgrade capacity',
            'WEATHER' => 'Wait for weather to improve and check infrastructure',
            'HUMAN_ERROR' => 'Review procedures and train staff',
            default => 'Investigate and determine appropriate action',
        };
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
