<?php

namespace App\Jobs\AI;

use App\Services\AI\AnomalyDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AnomalyDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 180;

    public function __construct(
        public readonly string $anomalyType,
        public readonly string $entityId,
        public readonly array $metrics,
        public readonly ?string $modelId = null
    ) {}

    public function handle(AnomalyDetectionService $anomalyService): void
    {
        Log::info("AnomalyDetectionJob: Processing {$this->anomalyType} anomaly detection for {$this->entityId}");

        try {
            $result = match ($this->anomalyType) {
                'network' => $anomalyService->detectNetworkAnomaly(
                    $this->entityId,
                    $this->metrics,
                    $this->modelId ? Uuid::fromString($this->modelId) : null
                ),
                'capacity' => $anomalyService->detectCapacityAnomaly(
                    $this->metrics['resource_type'] ?? 'unknown',
                    $this->entityId,
                    $this->metrics
                ),
                default => $anomalyService->detectNetworkAnomaly(
                    $this->entityId,
                    $this->metrics,
                    $this->modelId ? Uuid::fromString($this->modelId) : null
                )
            };

            Log::info("AnomalyDetectionJob: Completed anomaly detection", [
                'entity_id' => $this->entityId,
                'is_anomaly' => $result->isAnomaly,
                'score' => $result->score,
                'severity' => $result->severity,
            ]);

            if ($result->isAnomaly) {
                Log::warning("AnomalyDetectionJob: Anomaly detected!", [
                    'entity_id' => $this->entityId,
                    'severity' => $result->severity,
                    'recommendations' => $result->getRecommendations(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("AnomalyDetectionJob: Failed to detect anomaly", [
                'type' => $this->anomalyType,
                'entity_id' => $this->entityId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AnomalyDetectionJob: Anomaly detection permanently failed", [
            'type' => $this->anomalyType,
            'entity_id' => $this->entityId,
            'error' => $exception->getMessage()
        ]);
    }
}
