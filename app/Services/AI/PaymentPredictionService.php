<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;

class PaymentPredictionService
{
    private const LIKELY_TO_PAY_THRESHOLD = 0.6;

    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function predictPayment(
        string $customerId,
        string $invoiceId,
        array $paymentHistory,
        float $invoiceAmount
    ): array {
        Log::info("PaymentPrediction: Predicting payment for invoice {$invoiceId}");

        $features = $this->extractPaymentFeatures($customerId, $paymentHistory, $invoiceAmount);

        $mlResult = $this->aiConnector->classify(
            json_encode($features),
            'payment_prediction'
        );

        $probability = $mlResult['probability'] ?? $this->calculateRuleBasedProbability($features);
        $isLikelyToPay = $probability >= self::LIKELY_TO_PAY_THRESHOLD;
        $riskFactors = $this->identifyPaymentRiskFactors($features);
        $recommendedActions = $this->getPaymentRecommendations($probability, $riskFactors);

        return [
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'probability' => round($probability, 4),
            'is_likely_to_pay' => $isLikelyToPay,
            'risk_level' => $this->determineRiskLevel($probability),
            'risk_factors' => $riskFactors,
            'recommended_actions' => $recommendedActions,
            'predicted_payment_date' => $this->predictPaymentDate($probability),
            'confidence' => $mlResult['confidence'] ?? 0.8,
        ];
    }

    public function batchPredictPayments(array $predictions): array
    {
        $results = [];

        foreach ($predictions as $prediction) {
            $results[$prediction['invoice_id']] = $this->predictPayment(
                $prediction['customer_id'],
                $prediction['invoice_id'],
                $prediction['payment_history'],
                $prediction['invoice_amount']
            );
        }

        return $results;
    }

    private function extractPaymentFeatures(
        string $customerId,
        array $paymentHistory,
        float $invoiceAmount
    ): array {
        $totalPaid = 0;
        $totalLate = 0;
        $onTimeCount = 0;
        $lateCount = 0;

        foreach ($paymentHistory as $payment) {
            $totalPaid += $payment['amount'] ?? 0;

            if (($payment['is_late'] ?? false)) {
                $totalLate++;
                $lateCount++;
            } else {
                $onTimeCount++;
            }
        }

        $totalPayments = $onTimeCount + $lateCount;

        return [
            'customer_id' => $customerId,
            'invoice_amount' => $invoiceAmount,
            'total_payment_count' => $totalPayments,
            'on_time_count' => $onTimeCount,
            'late_count' => $lateCount,
            'late_payment_ratio' => $totalPayments > 0 ? $lateCount / $totalPayments : 0,
            'total_amount_paid' => $totalPaid,
            'average_payment_time' => $this->calculateAveragePaymentTime($paymentHistory),
        ];
    }

    private function calculateRuleBasedProbability(array $features): float
    {
        $probability = 0.85;

        $probability -= $features['late_payment_ratio'] * 0.4;

        if ($features['late_count'] >= 3) {
            $probability -= 0.2;
        } elseif ($features['late_count'] >= 1) {
            $probability -= 0.1;
        }

        if ($features['invoice_amount'] > 1000000) {
            $probability -= 0.05;
        }

        $avgPaymentTime = $features['average_payment_time'];
        if ($avgPaymentTime > 15) {
            $probability -= 0.15;
        } elseif ($avgPaymentTime > 7) {
            $probability -= 0.08;
        }

        return max(0.1, min(0.95, $probability));
    }

    private function identifyPaymentRiskFactors(array $features): array
    {
        $factors = [];

        if ($features['late_payment_ratio'] > 0.5) {
            $factors[] = 'High proportion of late payments';
        }

        if ($features['late_count'] >= 3) {
            $factors[] = 'Multiple late payments in history';
        }

        if ($features['average_payment_time'] > 15) {
            $factors[] = 'Slow payment behavior';
        }

        if ($features['invoice_amount'] > 5000000) {
            $factors[] = 'High invoice amount may delay payment';
        }

        return $factors;
    }

    private function determineRiskLevel(float $probability): string
    {
        return match (true) {
            $probability >= 0.8 => 'LOW',
            $probability >= 0.6 => 'MEDIUM',
            $probability >= 0.4 => 'HIGH',
            default => 'CRITICAL',
        };
    }

    private function getPaymentRecommendations(float $probability, array $riskFactors): array
    {
        $recommendations = [];

        if ($probability < 0.6) {
            $recommendations[] = 'Send payment reminder before due date';
            $recommendations[] = 'Offer flexible payment options';
        }

        if ($probability < 0.4) {
            $recommendations[] = 'Escalate to collections process';
            $recommendations[] = 'Consider temporary service suspension';
        }

        if ($probability >= 0.8) {
            $recommendations[] = 'Offer autopay discount for future invoices';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continue standard billing process';
        }

        return $recommendations;
    }

    private function predictPaymentDate(float $probability): string
    {
        $baseDays = 5;

        if ($probability < 0.4) {
            $daysToAdd = 25;
        } elseif ($probability < 0.6) {
            $daysToAdd = 15;
        } elseif ($probability < 0.8) {
            $daysToAdd = 7;
        } else {
            $daysToAdd = 3;
        }

        return now()->addDays($daysToAdd)->toDateString();
    }

    private function calculateAveragePaymentTime(array $paymentHistory): float
    {
        if (empty($paymentHistory)) {
            return 5.0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($paymentHistory as $payment) {
            if (isset($payment['days_to_pay'])) {
                $totalDays += $payment['days_to_pay'];
                $count++;
            }
        }

        return $count > 0 ? $totalDays / $count : 5.0;
    }
}
