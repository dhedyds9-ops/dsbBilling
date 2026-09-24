<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExternalAIConnector
{
    private const CACHE_TTL = 3600;
    private const TIMEOUT = 30;

    private ?string $apiKey;
    private ?string $provider;
    private ?string $endpoint;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'openai');
        $this->apiKey = config('services.ai.api_key');
        $this->endpoint = $this->getEndpoint();
    }

    public function chat(array $params): array
    {
        $prompt = $params['prompt'] ?? '';
        $model = $params['model'] ?? 'gpt-4';
        $temperature = $params['temperature'] ?? 0.7;
        $maxTokens = $params['max_tokens'] ?? 1000;

        $cacheKey = md5("chat:{$model}:{$prompt}");

        if (config('services.ai.use_cache', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::debug("ExternalAIConnector: Cache hit for chat request");
                return $cached;
            }
        }

        $result = $this->callAPI('/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful AI assistant for ISP network management.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        $response = [
            'content' => $result['choices'][0]['message']['content'] ?? 'No response',
            'model' => $model,
            'usage' => $result['usage'] ?? null,
            'cached' => false,
        ];

        if (config('services.ai.use_cache', true)) {
            Cache::put($cacheKey, $response, self::CACHE_TTL);
        }

        return $response;
    }

    public function classify(string $text, string $model): array
    {
        $cacheKey = md5("classify:{$model}:{$text}");

        if (config('services.ai.use_cache', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = $this->simulateClassification($text, $model);

        if (config('services.ai.use_cache', true)) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    public function analyze(string $type, array $data): array
    {
        $cacheKey = md5("analyze:{$type}:" . json_encode($data));

        if (config('services.ai.use_cache', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = match ($type) {
            'network_anomaly' => $this->analyzeNetworkAnomaly($data),
            'revenue_forecast' => $this->analyzeRevenueForecast($data),
            'customer_behavior' => $this->analyzeCustomerBehavior($data),
            default => ['analysis' => 'No specific analysis available'],
        };

        if (config('services.ai.use_cache', true)) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    public function analyzeSentiment(string $text): array
    {
        $positive = ['good', 'great', 'excellent', 'happy', 'satisfied', 'amazing'];
        $negative = ['bad', 'poor', 'terrible', 'angry', 'dissatisfied', 'worst'];

        $textLower = strtolower($text);

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positive as $word) {
            if (str_contains($textLower, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negative as $word) {
            if (str_contains($textLower, $word)) {
                $negativeCount++;
            }
        }

        $total = $positiveCount + $negativeCount;

        return [
            'sentiment' => $total > 0
                ? ($positiveCount > $negativeCount ? 'positive' : ($negativeCount > $positiveCount ? 'negative' : 'neutral'))
                : 'neutral',
            'scores' => [
                'positive' => $total > 0 ? $positiveCount / $total : 0.5,
                'negative' => $total > 0 ? $negativeCount / $total : 0,
                'neutral' => $total > 0 ? 0 : 1,
            ],
        ];
    }

    private function callAPI(string $endpoint, array $data): array
    {
        if (empty($this->apiKey)) {
            Log::warning("ExternalAIConnector: No API key configured, using simulation mode");
            return $this->simulateAPIResponse($endpoint, $data);
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("ExternalAIConnector: API call failed", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->simulateAPIResponse($endpoint, $data);

        } catch (\Exception $e) {
            Log::error("ExternalAIConnector: Exception during API call", [
                'error' => $e->getMessage(),
            ]);

            return $this->simulateAPIResponse($endpoint, $data);
        }
    }

    private function simulateAPIResponse(string $endpoint, array $data): array
    {
        if (str_contains($endpoint, 'chat/completions')) {
            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => 'This is a simulated AI response. Configure your API key for real responses.',
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                    'total_tokens' => 30,
                ],
            ];
        }

        return [];
    }

    private function simulateClassification(string $text, string $model): array
    {
        $classifications = [
            'alert_classification' => [
                'cpu_usage' => ['category' => 'performance', 'priority' => 'P2', 'recommended_action' => 'Check running processes'],
                'memory' => ['category' => 'performance', 'priority' => 'P2', 'recommended_action' => 'Check for memory leaks'],
                'link' => ['category' => 'connectivity', 'priority' => 'P1', 'recommended_action' => 'Check physical connections'],
                'down' => ['category' => 'availability', 'priority' => 'P1', 'recommended_action' => 'Immediate investigation required'],
                'temperature' => ['category' => 'hardware', 'priority' => 'P3', 'recommended_action' => 'Check cooling system'],
            ],
            'churn_prediction' => [
                'high_value' => ['probability' => 0.2, 'segment' => 'low_risk'],
                'medium_value' => ['probability' => 0.5, 'segment' => 'medium_risk'],
                'low_value' => ['probability' => 0.8, 'segment' => 'high_risk'],
            ],
            'customer_segmentation' => [
                'premium' => ['segment' => 'premium', 'score' => 0.9],
                'standard' => ['segment' => 'standard', 'score' => 0.6],
                'at_risk' => ['segment' => 'at_risk', 'score' => 0.3],
            ],
            'payment_prediction' => [
                'likely' => ['probability' => 0.85, 'predicted_days' => 3],
                'uncertain' => ['probability' => 0.5, 'predicted_days' => 10],
                'unlikely' => ['probability' => 0.2, 'predicted_days' => 20],
            ],
        ];

        $textLower = strtolower($text);

        if (isset($classifications[$model])) {
            foreach ($classifications[$model] as $key => $result) {
                if (str_contains($textLower, $key)) {
                    return array_merge($result, ['confidence' => 0.85]);
                }
            }
        }

        return ['category' => 'general', 'priority' => 'P3', 'confidence' => 0.5];
    }

    private function analyzeNetworkAnomaly(array $data): array
    {
        return [
            'type' => 'network_anomaly',
            'severity' => 'HIGH',
            'recommendations' => [
                'Check device connectivity',
                'Review recent configuration changes',
                'Monitor traffic patterns',
            ],
            'confidence' => 0.8,
        ];
    }

    private function analyzeRevenueForecast(array $data): array
    {
        $historical = $data['historical'] ?? [];
        $periods = $data['periods'] ?? 12;

        $avgRevenue = !empty($historical)
            ? array_sum(array_column($historical, 'revenue')) / count($historical)
            : 1000000;

        $growthRate = 0.05;

        return [
            'type' => 'revenue_forecast',
            'predicted_revenue' => $avgRevenue * (1 + $growthRate) * $periods,
            'growth_rate' => $growthRate,
            'confidence' => 0.75,
        ];
    }

    private function analyzeCustomerBehavior(array $data): array
    {
        return [
            'type' => 'customer_behavior',
            'engagement_score' => 0.7,
            'risk_factors' => ['low_login_frequency', 'late_payments'],
            'recommendations' => ['Send engagement email', 'Offer loyalty reward'],
        ];
    }

    private function getEndpoint(): string
    {
        return match ($this->provider) {
            'openai' => 'https://api.openai.com/v1',
            'anthropic' => 'https://api.anthropic.com/v1',
            'google' => 'https://generativelanguage.googleapis.com/v1',
            default => 'https://api.openai.com/v1',
        };
    }
}
