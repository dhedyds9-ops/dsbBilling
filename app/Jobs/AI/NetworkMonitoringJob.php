<?php

namespace App\Jobs\AI;

use App\Services\AI\NetworkAnomalyDetectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NetworkMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        public readonly array $deviceMetrics,
        public readonly ?string $modelId = null,
        public readonly bool $sendAlerts = true
    ) {}

    public function handle(NetworkAnomalyDetectorService $detectorService): void
    {
        Log::info("NetworkMonitoringJob: Processing " . count($this->deviceMetrics) . " device metrics");

        try {
            $results = $detectorService->batchDetectNetworkAnomalies($this->deviceMetrics, $this->modelId);

            $anomalies = array_filter($results, fn($r) => $r->isAnomaly);

            Log::info("NetworkMonitoringJob: Batch detection completed", [
                'total_devices' => count($this->deviceMetrics),
                'anomalies_detected' => count($anomalies),
            ]);

            if ($this->sendAlerts && count($anomalies) > 0) {
                foreach ($anomalies as $deviceId => $result) {
                    if (in_array($result->severity, ['HIGH', 'CRITICAL'])) {
                        Log::warning("NetworkMonitoringJob: Critical anomaly detected", [
                            'device_id' => $deviceId,
                            'severity' => $result->severity,
                            'score' => $result->score,
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("NetworkMonitoringJob: Batch detection failed", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("NetworkMonitoringJob: Network monitoring permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
