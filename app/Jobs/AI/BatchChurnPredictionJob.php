<?php

namespace App\Jobs\AI;

use App\Services\AI\ChurnPredictionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class BatchChurnPredictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300;
    public int $timeout = 1800;

    public function __construct(
        public readonly array $customerPredictions,
        public readonly ?string $modelId = null,
        public readonly bool $sendNotifications = true
    ) {}

    public function handle(ChurnPredictionService $churnService): void
    {
        Log::info("BatchChurnPredictionJob: Processing batch prediction for " . count($this->customerPredictions) . " customers");

        try {
            $results = $churnService->batchPredictChurn($this->customerPredictions, $this->modelId);

            $highRisk = array_filter($results, fn($r) => $r->riskLevel === 'HIGH' || $r->riskLevel === 'VERY_HIGH');

            Log::info("BatchChurnPredictionJob: Batch prediction completed", [
                'total_processed' => count($results),
                'high_risk_count' => count($highRisk),
                'high_risk_customers' => array_keys($highRisk),
            ]);

            if ($this->sendNotifications && count($highRisk) > 0) {
                foreach ($highRisk as $customerId => $result) {
                    Log::warning("BatchChurnPredictionJob: High churn risk detected", [
                        'customer_id' => $customerId,
                        'risk_level' => $result->riskLevel,
                        'churn_score' => $result->churnScore,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error("BatchChurnPredictionJob: Batch prediction failed", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("BatchChurnPredictionJob: Batch prediction permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
