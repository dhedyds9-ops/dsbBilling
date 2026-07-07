<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;

class CustomerSegmentationService
{
    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function segmentCustomer(array $customerData): array
    {
        Log::info("CustomerSegmentation: Segmenting customer {$customerData['customer_id'] ?? 'unknown'}");

        $demographicSegment = $this->segmentByDemographic($customerData);
        $behavioralSegment = $this->segmentByBehavioral($customerData);
        $valueSegment = $this->segmentByValue($customerData);

        $mlSegment = $this->aiConnector->classify(
            json_encode($customerData),
            'customer_segmentation'
        );

        return [
            'customer_id' => $customerData['customer_id'] ?? null,
            'segments' => [
                'demographic' => $demographicSegment,
                'behavioral' => $behavioralSegment,
                'value' => $valueSegment,
                'ml_segment' => $mlSegment['segment'] ?? 'standard',
            ],
            'primary_segment' => $this->determinePrimarySegment(
                $demographicSegment,
                $behavioralSegment,
                $valueSegment
            ),
            'recommended_actions' => $this->getSegmentRecommendations(
                $demographicSegment,
                $behavioralSegment,
                $valueSegment
            ),
        ];
    }

    public function batchSegmentCustomers(array $customers): array
    {
        $results = [];

        foreach ($customers as $customer) {
            $results[$customer['customer_id']] = $this->segmentCustomer($customer);
        }

        return $results;
    }

    private function segmentByDemographic(array $data): string
    {
        $age = $data['age'] ?? 30;
        $location = $data['location'] ?? 'urban';

        if ($age < 25) {
            return match ($location) {
                'urban' => 'young_urban',
                default => 'young_suburban',
            };
        } elseif ($age < 40) {
            return match ($location) {
                'urban' => 'professional_urban',
                default => 'professional_suburban',
            };
        } elseif ($age < 60) {
            return 'established';
        } else {
            return 'senior';
        }
    }

    private function segmentByBehavioral(array $data): string
    {
        $usageFrequency = $data['usage_frequency'] ?? 'medium';
        $serviceTier = $data['service_tier'] ?? 'basic';
        $engagementScore = $data['engagement_score'] ?? 0.5;

        if ($usageFrequency === 'high' && $engagementScore > 0.7) {
            return 'power_user';
        } elseif ($serviceTier === 'premium' || $serviceTier === 'enterprise') {
            return 'premium';
        } elseif ($usageFrequency === 'low' && $engagementScore < 0.3) {
            return 'at_risk';
        } else {
            return 'standard';
        }
    }

    private function segmentByValue(array $data): string
    {
        $monthlyRevenue = $data['monthly_revenue'] ?? 0;
        $tenureMonths = $data['tenure_months'] ?? 0;
        $churnProbability = $data['churn_probability'] ?? 0;

        $lifetimeValue = ($monthlyRevenue * $tenureMonths) * (1 - $churnProbability);

        return match (true) {
            $lifetimeValue > 10000000 => 'vip',
            $lifetimeValue > 3000000 => 'high_value',
            $lifetimeValue > 1000000 => 'medium_value',
            default => 'low_value',
        };
    }

    private function determinePrimarySegment(
        string $demographic,
        string $behavioral,
        string $value
    ): string {
        $segmentPriority = [
            'vip' => 5,
            'premium' => 4,
            'high_value' => 4,
            'power_user' => 4,
            'at_risk' => 3,
            'medium_value' => 3,
            'professional_urban' => 2,
            'established' => 2,
            'standard' => 1,
            'low_value' => 1,
        ];

        $segments = [$demographic, $behavioral, $value];
        $maxPriority = 0;
        $primary = 'standard';

        foreach ($segments as $segment) {
            $priority = $segmentPriority[$segment] ?? 0;
            if ($priority > $maxPriority) {
                $maxPriority = $priority;
                $primary = $segment;
            }
        }

        return $primary;
    }

    private function getSegmentRecommendations(
        string $demographic,
        string $behavioral,
        string $value
    ): array {
        $recommendations = [];

        if ($value === 'vip' || $value === 'high_value') {
            $recommendations[] = 'Offer exclusive loyalty rewards';
            $recommendations[] = 'Provide dedicated account manager';
        }

        if ($behavioral === 'at_risk') {
            $recommendations[] = 'Implement churn prevention campaign';
            $recommendations[] = 'Offer retention incentives';
        }

        if ($behavioral === 'power_user') {
            $recommendations[] = 'Promote premium upgrades';
            $recommendations[] = 'Introduce new features early access';
        }

        if (str_contains($demographic, 'young')) {
            $recommendations[] = 'Highlight mobile and streaming features';
            $recommendations[] = 'Use social media for communication';
        }

        return $recommendations;
    }
}
