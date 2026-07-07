<?php

namespace Src\Domain\AI\ValueObjects;

class AIMessage
{
    public function __construct(
        public readonly string $role, // user, assistant, system
        public readonly string $content,
        public readonly ?array $metadata = null,
        public readonly ?string $model = null,
        public readonly ?float $confidence = null
    ) {}

    public static function user(string $content, ?array $metadata = null): self
    {
        return new self(role: 'user', content: $content, metadata: $metadata);
    }

    public static function assistant(string $content, ?array $metadata = null, ?float $confidence = null): self
    {
        return new self(role: 'assistant', content: $content, metadata: $metadata, confidence: $confidence);
    }

    public static function system(string $content, ?array $metadata = null): self
    {
        return new self(role: 'system', content: $content, metadata: $metadata);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isSystem(): bool
    {
        return $this->role === 'system';
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
            'metadata' => $this->metadata,
            'model' => $this->model,
            'confidence' => $this->confidence,
        ];
    }
}

class PredictionInput
{
    public function __construct(
        public readonly string $modelType,
        public readonly array $features,
        public readonly ?string $entityType = null,
        public readonly ?string $entityId = null
    ) {}

    public function toArray(): array
    {
        return [
            'model_type' => $this->modelType,
            'features' => $this->features,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
        ];
    }
}

class PredictionResult
{
    public function __construct(
        public readonly string $predictionId,
        public readonly mixed $prediction,
        public readonly float $confidence,
        public readonly array $metadata = [],
        public readonly ?array $probabilities = null
    ) {}

    public function getProbability(string $class): ?float
    {
        return $this->probabilities[$class] ?? null;
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence >= 0.8;
    }

    public function isLowConfidence(): bool
    {
        return $this->confidence < 0.5;
    }

    public function toArray(): array
    {
        return [
            'prediction_id' => $this->predictionId,
            'prediction' => $this->prediction,
            'confidence' => $this->confidence,
            'metadata' => $this->metadata,
            'probabilities' => $this->probabilities,
        ];
    }
}

class AnomalyResult
{
    public function __construct(
        public readonly string $anomalyId,
        public readonly bool $isAnomaly,
        public readonly float $score,
        public readonly string $severity,
        public readonly ?string $type = null,
        public readonly ?string $description = null,
        public readonly array $details = []
    ) {}

    public function getRecommendations(): array
    {
        // Generate recommendations based on anomaly type and severity
        $recommendations = [];

        if ($this->severity === 'critical' || $this->severity === 'high') {
            $recommendations[] = 'Immediate action required';
            $recommendations[] = 'Consider auto-creating incident ticket';
        }

        if ($this->type === 'network') {
            $recommendations[] = 'Check network device connectivity';
            $recommendations[] = 'Verify cable connections';
        } elseif ($this->type === 'capacity') {
            $recommendations[] = 'Consider capacity expansion';
            $recommendations[] = 'Review current utilization thresholds';
        } elseif ($this->type === 'performance') {
            $recommendations[] = 'Profile application performance';
            $recommendations[] = 'Check for resource bottlenecks';
        }

        return $recommendations;
    }

    public function toArray(): array
    {
        return [
            'anomaly_id' => $this->anomalyId,
            'is_anomaly' => $this->isAnomaly,
            'score' => $this->score,
            'severity' => $this->severity,
            'type' => $this->type,
            'description' => $this->description,
            'details' => $this->details,
            'recommendations' => $this->getRecommendations(),
        ];
    }
}

class ChurnPredictionResult
{
    public function __construct(
        public readonly string $customerId,
        public readonly float $churnScore,
        public readonly string $riskLevel,
        public readonly array $riskFactors,
        public readonly array $recommendedActions,
        public readonly ?\DateTimeImmutable $predictionDate = null
    ) {}

    public static function fromPredictionResult(string $customerId, PredictionResult $result): self
    {
        $churnScore = is_numeric($result->prediction) ? (float)$result->prediction : $result->confidence;
        $riskLevel = \Src\Domain\AI\Enums\ChurnRiskLevel::fromScore($churnScore)->value;
        $riskFactors = $result->metadata['risk_factors'] ?? [];
        $recommendedActions = self::generateRecommendedActions($riskLevel, $riskFactors);

        return new self(
            customerId: $customerId,
            churnScore: $churnScore,
            riskLevel: $riskLevel,
            riskFactors: $riskFactors,
            recommendedActions: $recommendedActions,
            predictionDate: new \DateTimeImmutable()
        );
    }

    private static function generateRecommendedActions(string $riskLevel, array $riskFactors): array
    {
        $actions = [];

        if ($riskLevel === 'very_high' || $riskLevel === 'high') {
            $actions[] = 'Immediately contact customer';
            $actions[] = 'Offer retention discount';
            $actions[] = 'Schedule manager call';
            $actions[] = 'Review recent complaints';
        } elseif ($riskLevel === 'medium') {
            $actions[] = 'Send satisfaction survey';
            $actions[] = 'Offer loyalty rewards';
            $actions[] = 'Schedule proactive support';
        } else {
            $actions[] = 'Continue regular engagement';
            $actions[] = 'Monitor usage patterns';
        }

        // Add factor-specific actions
        if (in_array('late_payments', $riskFactors)) {
            $actions[] = 'Review billing history';
        }
        if (in_array('low_usage', $riskFactors)) {
            $actions[] = 'Promote higher-tier packages';
        }
        if (in_array('complaints', $riskFactors)) {
            $actions[] = 'Address pending tickets';
        }

        return $actions;
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'churn_score' => $this->churnScore,
            'risk_level' => $this->riskLevel,
            'risk_factors' => $this->riskFactors,
            'recommended_actions' => $this->recommendedActions,
            'prediction_date' => $this->predictionDate?->format('Y-m-d H:i:s'),
        ];
    }
}
