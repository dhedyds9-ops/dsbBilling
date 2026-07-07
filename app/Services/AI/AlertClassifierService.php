<?php

namespace App\Services\AI;

use App\Services\AI\ExternalAIConnector;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AlertClassifierService
{
    public function __construct(
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function classifyAlert(
        string $alertId,
        string $deviceId,
        string $alertType,
        string $message,
        array $context = []
    ): array {
        Log::info("AlertClassifier: Classifying alert {$alertId}");

        $prompt = $this->buildClassificationPrompt($alertType, $message, $context);

        $result = $this->aiConnector->classify($message, 'alert_classification');

        return [
            'alert_id' => $alertId,
            'category' => $result['category'] ?? $this->inferCategory($alertType),
            'priority' => $result['priority'] ?? $this->inferPriority($alertType),
            'recommended_action' => $result['recommended_action'] ?? $this->getRecommendedAction($alertType),
            'confidence' => $result['confidence'] ?? 0.85,
        ];
    }

    public function batchClassifyAlerts(array $alerts): array
    {
        $results = [];

        foreach ($alerts as $alert) {
            $results[$alert['id']] = $this->classifyAlert(
                $alert['id'],
                $alert['device_id'],
                $alert['type'],
                $alert['message'],
                $alert['context'] ?? []
            );
        }

        return $results;
    }

    private function buildClassificationPrompt(string $alertType, string $message, array $context): string
    {
        return "Classify the following network alert:\n\n" .
               "Type: {$alertType}\n" .
               "Message: {$message}\n" .
               "Context: " . json_encode($context);
    }

    private function inferCategory(string $alertType): string
    {
        return match (true) {
            str_contains(strtolower($alertType), 'link') => 'connectivity',
            str_contains(strtolower($alertType), 'cpu') || str_contains(strtolower($alertType), 'memory') => 'performance',
            str_contains(strtolower($alertType), 'temperature') => 'hardware',
            str_contains(strtolower($alertType), 'auth') || str_contains(strtolower($alertType), 'security') => 'security',
            default => 'general',
        };
    }

    private function inferPriority(string $alertType): string
    {
        return match (true) {
            str_contains(strtolower($alertType), 'critical') || str_contains(strtolower($alertType), 'down') => 'P1',
            str_contains(strtolower($alertType), 'warning') => 'P2',
            str_contains(strtolower($alertType), 'info') => 'P4',
            default => 'P3',
        };
    }

    private function getRecommendedAction(string $alertType): string
    {
        return match (true) {
            str_contains(strtolower($alertType), 'link') => 'Check physical connections and interface status',
            str_contains(strtolower($alertType), 'cpu') => 'Investigate processes consuming CPU resources',
            str_contains(strtolower($alertType), 'memory') => 'Check for memory leaks or increased traffic',
            str_contains(strtolower($alertType), 'temperature') => 'Verify cooling system operation',
            default => 'Review alert details and take appropriate action',
        };
    }
}
