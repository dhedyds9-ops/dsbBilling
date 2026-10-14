<?php

namespace Src\Domain\AI;

use Src\Domain\AI\Enums\ChurnRiskLevel;
use Src\Domain\AI\Enums\PredictionStatus;
use Src\Domain\AI\ValueObjects\ChurnPredictionResult;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AIPrediction extends AggregateRoot
{
    private ?float $score = null;
    private ?array $probabilities = null;
    private ?array $riskFactors = null;
    private ?array $recommendations = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly string $modelType,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly PredictionStatus $status,
        public readonly ?Uuid $modelId = null,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
        public readonly ?DateTimeImmutable $completedAt = null
    ) {}

    public static function create(
        string $modelType,
        string $entityType,
        string $entityId,
        ?Uuid $modelId = null,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            modelType: $modelType,
            entityType: $entityType,
            entityId: $entityId,
            status: PredictionStatus::PENDING,
            modelId: $modelId,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function markAsProcessing(): void
    {
        $this->status = PredictionStatus::PROCESSING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsCompleted(
        float $score,
        ?array $probabilities = null,
        ?array $riskFactors = null,
        ?array $recommendations = null
    ): void {
        $this->score = $score;
        $this->probabilities = $probabilities;
        $this->riskFactors = $riskFactors;
        $this->recommendations = $recommendations;
        $this->status = PredictionStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = PredictionStatus::FAILED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getProbabilities(): ?array
    {
        return $this->probabilities;
    }

    public function getRiskFactors(): ?array
    {
        return $this->riskFactors;
    }

    public function getRecommendations(): ?array
    {
        return $this->recommendations;
    }

    public function getChurnRiskLevel(): ?ChurnRiskLevel
    {
        if ($this->score === null) {
            return null;
        }

        if ($this->modelType !== 'churn_prediction') {
            return null;
        }

        return ChurnRiskLevel::fromScore($this->score);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'model_type' => $this->modelType,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'status' => $this->status->value,
            'model_id' => $this->modelId?->toString(),
            'score' => $this->score,
            'probabilities' => $this->probabilities,
            'risk_factors' => $this->riskFactors,
            'recommendations' => $this->recommendations,
            'risk_level' => $this->getChurnRiskLevel()?->value,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
