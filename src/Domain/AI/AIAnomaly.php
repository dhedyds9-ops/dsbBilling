<?php

namespace Src\Domain\AI;

use Src\Domain\AI\Enums\AnomalySeverity;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AIAnomaly extends AggregateRoot
{
    private ?float $score = null;
    private ?string $type = null;
    private ?string $description = null;
    private array $details = [];
    private array $recommendations = [];
    private bool $acknowledged = false;
    private ?Uuid $acknowledgedBy = null;
    private ?DateTimeImmutable $acknowledgedAt = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly string $detectorType, // network, capacity, fraud
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly bool $isAnomaly,
        public readonly ?Uuid $modelId = null,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null
    ) {}

    public static function create(
        string $detectorType,
        string $entityType,
        string $entityId,
        bool $isAnomaly,
        ?Uuid $modelId = null,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            detectorType: $detectorType,
            entityType: $entityType,
            entityId: $entityId,
            isAnomaly: $isAnomaly,
            modelId: $modelId,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable()
        );
    }

    public function setScore(float $score): void
    {
        $this->score = $score;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function setRecommendations(array $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    public function acknowledge(Uuid $acknowledgedBy): void
    {
        $this->acknowledged = true;
        $this->acknowledgedBy = $acknowledgedBy;
        $this->acknowledgedAt = new DateTimeImmutable();
    }

    public function getSeverity(): AnomalySeverity
    {
        if ($this->score === null) {
            return AnomalySeverity::LOW;
        }

        if ($this->score >= 0.9) {
            return AnomalySeverity::CRITICAL;
        } elseif ($this->score >= 0.7) {
            return AnomalySeverity::HIGH;
        } elseif ($this->score >= 0.5) {
            return AnomalySeverity::MEDIUM;
        }

        return AnomalySeverity::LOW;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'detector_type' => $this->detectorType,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'is_anomaly' => $this->isAnomaly,
            'score' => $this->score,
            'severity' => $this->getSeverity()->value,
            'type' => $this->type,
            'description' => $this->description,
            'details' => $this->details,
            'recommendations' => $this->recommendations,
            'acknowledged' => $this->acknowledged,
            'acknowledged_by' => $this->acknowledgedBy?->toString(),
            'acknowledged_at' => $this->acknowledgedAt?->format('Y-m-d H:i:s'),
            'model_id' => $this->modelId?->toString(),
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
