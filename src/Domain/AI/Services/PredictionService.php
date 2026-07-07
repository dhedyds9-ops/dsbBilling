<?php

namespace Src\Domain\AI\Services;

use Src\Domain\AI\AIPrediction;
use Src\Domain\AI\Repositories\AIPredictionRepositoryInterface;
use Src\Domain\AI\Repositories\AIModelRepositoryInterface;
use Src\Domain\AI\Events\PredictionCompleted;
use Src\Domain\AI\Enums\ChurnRiskLevel;
use Src\Domain\AI\Enums\PredictionStatus;
use Src\Domain\AI\ValueObjects\PredictionInput;
use Src\Domain\AI\ValueObjects\PredictionResult;
use Src\Domain\AI\ValueObjects\ChurnPredictionResult;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class PredictionService
{
    public function __construct(
        private readonly AIPredictionRepositoryInterface $predictionRepository,
        private readonly AIModelRepositoryInterface $modelRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MLModelExecutor $mlExecutor
    ) {}

    /**
     * Create and execute churn prediction for a customer
     */
    public function predictChurn(
        string $customerId,
        array $customerFeatures,
        ?Uuid $modelId = null
    ): ChurnPredictionResult {
        // Create prediction record
        $prediction = AIPrediction::create(
            modelType: 'churn_prediction',
            entityType: 'customer',
            entityId: $customerId,
            modelId: $modelId
        );

        $prediction->markAsProcessing();
        $this->predictionRepository->save($prediction);

        try {
            // Get the model
            $model = $modelId
                ? $this->modelRepository->findById($modelId)
                : $this->modelRepository->findActiveByType(\Src\Domain\AI\Enums\AIModelType::CHURN_PREDICTION);

            if (!$model) {
                throw new \DomainException('No churn prediction model available');
            }

            // Execute prediction
            $input = new PredictionInput(
                modelType: 'churn_prediction',
                features: $customerFeatures,
                entityType: 'customer',
                entityId: $customerId
            );

            $result = $this->mlExecutor->execute($model, $input);

            // Extract risk factors
            $riskFactors = $this->extractChurnRiskFactors($customerFeatures, $result);

            // Generate recommendations based on risk level
            $recommendations = $this->generateChurnRecommendations($result->prediction, $riskFactors);

            // Update prediction record
            $prediction->markAsCompleted(
                score: (float) $result->prediction,
                probabilities: $result->probabilities,
                riskFactors: $riskFactors,
                recommendations: $recommendations
            );

            $this->predictionRepository->save($prediction);

            // Dispatch event
            $this->eventDispatcher->dispatch(new PredictionCompleted(
                predictionId: $prediction->id,
                entityType: 'customer',
                entityId: $customerId,
                score: $result->prediction,
                riskLevel: ChurnRiskLevel::fromScore((float) $result->prediction)->value
            ));

            return new ChurnPredictionResult(
                customerId: $customerId,
                churnScore: (float) $result->prediction,
                riskLevel: ChurnRiskLevel::fromScore((float) $result->prediction)->value,
                riskFactors: $riskFactors,
                recommendedActions: $recommendations
            );

        } catch (\Exception $e) {
            $prediction->markAsFailed();
            $this->predictionRepository->save($prediction);
            throw $e;
        }
    }

    /**
     * Batch predict churn for multiple customers
     */
    public function batchPredictChurn(array $customerPredictions): array
    {
        $results = [];

        foreach ($customerPredictions as $customerId => $features) {
            try {
                $results[$customerId] = $this->predictChurn($customerId, $features);
            } catch (\Exception $e) {
                $results[$customerId] = [
                    'error' => $e->getMessage(),
                    'customer_id' => $customerId,
                ];
            }
        }

        return $results;
    }

    /**
     * Predict revenue forecast
     */
    public function predictRevenue(
        array $historicalData,
        int $forecastPeriods = 12,
        ?Uuid $modelId = null
    ): array {
        $model = $modelId
            ? $this->modelRepository->findById($modelId)
            : $this->modelRepository->findActiveByType(\Src\Domain\AI\Enums\AIModelType::REVENUE_FORECAST);

        if (!$model) {
            throw new \DomainException('No revenue forecast model available');
        }

        $input = new PredictionInput(
            modelType: 'revenue_forecast',
            features: [
                'historical_data' => $historicalData,
                'forecast_periods' => $forecastPeriods,
            ]
        );

        $result = $this->mlExecutor->execute($model, $input);

        return [
            'predictions' => $result->prediction,
            'confidence' => $result->confidence,
            'model' => $model->name,
            'periods' => $forecastPeriods,
        ];
    }

    /**
     * Predict payment behavior
     */
    public function predictPayment(
        string $customerId,
        array $paymentHistory,
        float $invoiceAmount
    ): array {
        $model = $this->modelRepository->findActiveByType(\Src\Domain\AI\Enums\AIModelType::PAYMENT_PREDICTION);

        if (!$model) {
            // Fallback to rule-based prediction
            return $this->ruleBasedPaymentPrediction($paymentHistory, $invoiceAmount);
        }

        $input = new PredictionInput(
            modelType: 'payment_prediction',
            features: [
                'payment_history' => $paymentHistory,
                'invoice_amount' => $invoiceAmount,
            ],
            entityType: 'customer',
            entityId: $customerId
        );

        $result = $this->mlExecutor->execute($model, $input);

        return [
            'will_pay_on_time' => (bool) $result->prediction,
            'probability' => $result->prediction,
            'confidence' => $result->confidence,
            'risk_level' => $result->prediction < 0.5 ? 'high' : 'low',
        ];
    }

    /**
     * Extract churn risk factors from customer features
     */
    private function extractChurnRiskFactors(array $features, PredictionResult $result): array
    {
        $riskFactors = [];

        // Late payments indicator
        if (isset($features['late_payment_count']) && $features['late_payment_count'] > 2) {
            $riskFactors[] = 'late_payments';
        }

        // Low usage indicator
        if (isset($features['usage_ratio']) && $features['usage_ratio'] < 0.3) {
            $riskFactors[] = 'low_usage';
        }

        // Support tickets indicator
        if (isset($features['ticket_count_last_30_days']) && $features['ticket_count_last_30_days'] > 5) {
            $riskFactors[] = 'high_tickets';
        }

        // Complaints indicator
        if (isset($features['complaint_count']) && $features['complaint_count'] > 0) {
            $riskFactors[] = 'complaints';
        }

        // Tenure indicator
        if (isset($features['tenure_months']) && $features['tenure_months'] < 6) {
            $riskFactors[] = 'new_customer';
        }

        // Package downgrades
        if (isset($features['downgrade_count']) && $features['downgrade_count'] > 0) {
            $riskFactors[] = 'package_downgrade';
        }

        // From ML model metadata
        if (isset($result->metadata['feature_importance'])) {
            // Add top risk factors from model
            $importance = $result->metadata['feature_importance'];
            arsort($importance);
            $topFactors = array_slice(array_keys($importance), 0, 3);
            foreach ($topFactors as $factor) {
                if (!in_array($factor, $riskFactors)) {
                    $riskFactors[] = $factor;
                }
            }
        }

        return $riskFactors;
    }

    /**
     * Generate recommendations based on churn score and risk factors
     */
    private function generateChurnRecommendations(float $churnScore, array $riskFactors): array
    {
        $riskLevel = ChurnRiskLevel::fromScore($churnScore);
        $recommendations = [];

        if ($riskLevel === ChurnRiskLevel::VERY_HIGH) {
            $recommendations[] = 'Immediately contact customer for retention discussion';
            $recommendations[] = 'Offer personalized retention package';
            $recommendations[] = 'Schedule manager-level follow-up';
            $recommendations[] = 'Review recent service issues and resolve';
        } elseif ($riskLevel === ChurnRiskLevel::HIGH) {
            $recommendations[] = 'Proactive outreach within 48 hours';
            $recommendations[] = 'Offer loyalty discount';
            $recommendations[] = 'Conduct satisfaction survey';
        } elseif ($riskLevel === ChurnRiskLevel::MEDIUM) {
            $recommendations[] = 'Send retention offer';
            $recommendations[] = 'Monitor usage patterns closely';
            $recommendations[] = 'Schedule periodic check-ins';
        } else {
            $recommendations[] = 'Continue regular engagement';
            $recommendations[] = 'Occasional appreciation messages';
        }

        // Factor-specific recommendations
        if (in_array('late_payments', $riskFactors)) {
            array_unshift($recommendations, 'Review billing issues and offer auto-pay discount');
        }

        if (in_array('low_usage', $riskFactors)) {
            $recommendations[] = 'Promote higher-speed packages or add-on services';
        }

        if (in_array('complaints', $riskFactors)) {
            $recommendations[] = 'Address all pending complaints immediately';
        }

        if (in_array('high_tickets', $riskFactors)) {
            $recommendations[] = 'Schedule proactive technical support';
        }

        return $recommendations;
    }

    /**
     * Rule-based fallback for payment prediction
     */
    private function ruleBasedPaymentPrediction(array $paymentHistory, float $invoiceAmount): array
    {
        $onTimeCount = 0;
        $lateCount = 0;
        $totalAmount = 0;

        foreach ($paymentHistory as $payment) {
            if ($payment['is_on_time'] ?? false) {
                $onTimeCount++;
            } else {
                $lateCount++;
            }
            $totalAmount += $payment['amount'] ?? 0;
        }

        $totalPayments = $onTimeCount + $lateCount;
        $onTimeRate = $totalPayments > 0 ? $onTimeCount / $totalPayments : 1.0;

        // Higher invoice relative to average might reduce likelihood
        $avgPayment = $totalPayments > 0 ? $totalAmount / $totalPayments : 0;
        $highAmountFactor = $avgPayment > 0 && $invoiceAmount > $avgPayment * 1.5 ? 0.8 : 1.0;

        $probability = $onTimeRate * $highAmountFactor;

        return [
            'will_pay_on_time' => $probability >= 0.5,
            'probability' => $probability,
            'confidence' => 0.7, // Lower confidence for rule-based
            'risk_level' => $probability < 0.5 ? 'high' : ($probability < 0.7 ? 'medium' : 'low'),
        ];
    }
}

class MLModelExecutor
{
    public function execute(AIModel $model, PredictionInput $input): PredictionResult
    {
        // Placeholder for actual ML model execution
        // In production, this would interface with TensorFlow, PyTorch, or ML services

        $features = $input->features;

        // Simulate prediction based on model type
        switch ($model->type->value) {
            case 'churn_prediction':
                $prediction = $this->simulateChurnPrediction($features);
                break;
            case 'revenue_forecast':
                $prediction = $this->simulateRevenueForecast($features);
                break;
            case 'payment_prediction':
                $prediction = $this->simulatePaymentPrediction($features);
                break;
            default:
                $prediction = 0.5;
        }

        return new PredictionResult(
            predictionId: Uuid::generate()->toString(),
            prediction: $prediction,
            confidence: $model->getAccuracy() ?? 0.85,
            metadata: [
                'model_id' => $model->id->toString(),
                'model_version' => $model->version,
            ],
            probabilities: [
                'churn' => $prediction,
                'no_churn' => 1 - $prediction,
            ]
        );
    }

    private function simulateChurnPrediction(array $features): float
    {
        // Simplified simulation - in reality would use actual ML model
        $score = 0.2; // Base score

        if (isset($features['late_payment_count'])) {
            $score += min(0.3, $features['late_payment_count'] * 0.1);
        }

        if (isset($features['usage_ratio'])) {
            $score += (1 - $features['usage_ratio']) * 0.3;
        }

        if (isset($features['ticket_count_last_30_days'])) {
            $score += min(0.2, $features['ticket_count_last_30_days'] * 0.04);
        }

        if (isset($features['tenure_months'])) {
            $score -= min(0.1, $features['tenure_months'] * 0.02);
        }

        return min(1.0, max(0.0, $score));
    }

    private function simulateRevenueForecast(array $features): array
    {
        $historicalData = $features['historical_data'] ?? [];
        $periods = $features['forecast_periods'] ?? 12;

        if (empty($historicalData)) {
            return array_fill(0, $periods, 0);
        }

        $lastValue = end($historicalData);
        $trend = $this->calculateTrend($historicalData);

        $predictions = [];
        for ($i = 1; $i <= $periods; $i++) {
            $predictions[] = $lastValue * pow(1 + $trend, $i);
        }

        return $predictions;
    }

    private function simulatePaymentPrediction(array $features): float
    {
        $paymentHistory = $features['payment_history'] ?? [];

        $onTimeCount = 0;
        $total = count($paymentHistory);

        foreach ($paymentHistory as $payment) {
            if ($payment['is_on_time'] ?? false) {
                $onTimeCount++;
            }
        }

        return $total > 0 ? $onTimeCount / $total : 0.8;
    }

    private function calculateTrend(array $data): float
    {
        if (count($data) < 2) {
            return 0;
        }

        $first = $data[0];
        $last = end($data);
        $n = count($data);

        if ($first == 0) {
            return 0;
        }

        // Simple compound annual growth rate
        return (pow($last / $first, 1 / $n) - 1);
    }
}
