<?php

namespace App\Jobs\AI;

use App\Services\AI\PredictionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PredictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        public readonly string $predictionType,
        public readonly string $entityId,
        public readonly array $features,
        public readonly ?string $modelId = null
    ) {}

    public function handle(PredictionService $predictionService): void
    {
        Log::info("PredictionJob: Processing {$this->predictionType} prediction for {$this->entityId}");

        try {
            $result = match ($this->predictionType) {
                'churn' => $predictionService->predictChurn(
                    $this->entityId,
                    $this->features,
                    $this->modelId ? Uuid::fromString($this->modelId) : null
                ),
                'revenue' => $predictionService->predictRevenue(
                    $this->features,
                    $this->features['periods'] ?? 12,
                    $this->modelId ? Uuid::fromString($this->modelId) : null
                ),
                'payment' => $predictionService->predictPayment(
                    $this->entityId,
                    $this->features['history'] ?? [],
                    $this->features['amount'] ?? 0
                ),
                default => throw new \InvalidArgumentException("Unknown prediction type: {$this->predictionType}")
            };

            Log::info("PredictionJob: Completed {$this->predictionType} prediction", [
                'entity_id' => $this->entityId,
                'confidence' => $result->confidence ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("PredictionJob: Failed to process prediction", [
                'type' => $this->predictionType,
                'entity_id' => $this->entityId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("PredictionJob: Prediction permanently failed", [
            'type' => $this->predictionType,
            'entity_id' => $this->entityId,
            'error' => $exception->getMessage()
        ]);
    }
}
