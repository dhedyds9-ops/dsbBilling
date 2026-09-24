<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;

class ChurnPredictionService
{
    private const HIGH_RISK_THRESHOLD = 0.7;
    private const MEDIUM_RISK_THRESHOLD = 0.4;

    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function predictChurn(
        string $customerId,
        array $customerData,
        ?string $modelId = null
    ): array {
        Log::info("ChurnPrediction: Predicting churn for customer {$customerId}");

        $features = $this->extractChurnFeatures($customerData);

        $mlResult = $this->aiConnector->classify(
            json_encode($features),
            'churn_prediction'
        );

        $churnScore = $mlResult['probability'] ?? $this->calculateRuleBasedScore($features);
        $riskLevel = $this->determineRiskLevel($churnScore);
        $riskFactors = $this->identifyRiskFactors($features);
        $recommendedActions = $this->getRecommendedActions($riskLevel, $riskFactors);

        return [
            'customer_id' => $customerId,
            'churn_score' => round($churnScore, 4),
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'recommended_actions' => $recommendedActions,
            'model_id' => $modelId,
            'predicted_at' => now()->toIso8601String(),
        ];
    }

    public function batchPredictChurn(array $customerPredictions, ?string $modelId = null): array
    {
        $results = [];

        foreach ($customerPredictions as $customerId => $customerData) {
            $results[$customerId] = (object) $this->predictChurn($customerId, $customerData, $modelId);
        }

        return $results;
    }

    private function extractChurnFeatures(array $customerData): array
    {
        return [
            'tenure_months' => $customerData['tenure_months'] ?? 0,
            'monthly_charge' => $customerData['monthly_charge'] ?? 0,
            'late_payment_count' => $customerData['late_payment_count'] ?? 0,
            'support_tickets' => $customerData['support_tickets'] ?? 0,
            'service_downtime' => $customerData['service_downtime'] ?? 0,
            'plan_downgrades' => $customerData['plan_downgrades'] ?? 0,
            'complaint_count' => $customerData['complaint_count'] ?? 0,
            'login_frequency' => $customerData['login_frequency'] ?? 0,
            'package_change_count' => $customerData['package_change_count'] ?? 0,
            'payment_method_stability' => $customerData['payment_method_stability'] ?? 1,
        ];
    }

    private function calculateRuleBasedScore(array $features): float
    {
        $score = 0.0;

        $score += min(0.3, $features['late_payment_count'] * 0.08);
        $score += min(0.2, $features['support_tickets'] * 0.04);
        $score += min(0.2, $features['complaint_count'] * 0.05);

        if ($features['tenure_months'] < 6) {
            $score += 0.15;
        } elseif ($features['tenure_months'] < 12) {
            $score += 0.08;
        }

        if ($features['monthly_charge'] > 500000) {
            $score += 0.1;
        }

        if ($features['service_downtime'] > 24) {
            $score += 0.15;
        }

        if ($features['login_frequency'] < 2) {
            $score += 0.1;
        }

        return min(1.0, $score);
    }

    private function determineRiskLevel(float $churnScore): string
    {
        return match (true) {
            $churnScore >= self::HIGH_RISK_THRESHOLD => 'VERY_HIGH',
            $churnScore >= self::MEDIUM_RISK_THRESHOLD => 'HIGH',
            $churnScore >= 0.2 => 'MEDIUM',
            default => 'LOW',
        };
    }

    private function identifyRiskFactors(array $features): array
    {
        $factors = [];

        if ($features['late_payment_count'] > 2) {
            $factors[] = 'Frequent late payments';
        }

        if ($features['support_tickets'] > 3) {
            $factors[] = 'High support ticket volume';
        }

        if ($features['service_downtime'] > 12) {
            $factors[] = 'Experienced prolonged service outages';
        }

        if ($features['complaint_count'] > 2) {
            $factors[] = 'Multiple complaints filed';
        }

        if ($features['tenure_months'] < 6) {
            $factors[] = 'New customer with unestablished loyalty';
        }

        if ($features['login_frequency'] < 2) {
            $factors[] = 'Low platform engagement';
        }

        if ($features['plan_downgrades'] > 0) {
            $factors[] = 'Downgraded service plan';
        }

        return $factors;
    }

    private function getRecommendedActions(string $riskLevel, array $riskFactors): array
    {
        $actions = [];

        if (in_array('Frequent late payments', $riskFactors)) {
            $actions[] = 'Send payment reminder and offer autopay discount';
        }

        if (in_array('High support ticket volume', $riskFactors)) {
            $actions[] = 'Schedule proactive check-in call';
        }

        if (in_array('Experienced prolonged service outages', $riskFactors)) {
            $actions[] = 'Offer service credit and compensation';
        }

        if (in_array('Low platform engagement', $riskFactors)) {
            $actions[] = 'Send usage tips and feature highlights';
        }

        $actions[] = match ($riskLevel) {
            'VERY_HIGH' => 'Escalate to retention specialist immediately',
            'HIGH' => 'Schedule retention call within 48 hours',
            'MEDIUM' => 'Send satisfaction survey',
            default => 'Continue regular engagement',
        };

        return $actions;
    }
}
