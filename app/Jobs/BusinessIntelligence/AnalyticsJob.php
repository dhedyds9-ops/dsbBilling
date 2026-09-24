<?php

namespace App\Jobs\BusinessIntelligence;

use App\Services\BusinessIntelligence\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;

class AnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        public readonly string $analyticsId,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null
    ) {}

    public function handle(AnalyticsService $analyticsService): void
    {
        Log::info("AnalyticsJob: Processing analytics {$this->analyticsId}");

        try {
            $timeRange = null;

            if ($this->startDate && $this->endDate) {
                $timeRange = TimeRange::create(
                    startDate: new \DateTimeImmutable($this->startDate),
                    endDate: new \DateTimeImmutable($this->endDate)
                );
            }

            $results = $analyticsService->computeAnalytics(
                \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->analyticsId)
            );

            Log::info("AnalyticsJob: Completed analytics {$this->analyticsId}", [
                'metrics_count' => count($results)
            ]);

        } catch (\Exception $e) {
            Log::error("AnalyticsJob: Failed to process analytics {$this->analyticsId}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyticsJob: Analytics {$this->analyticsId} failed permanently", [
            'error' => $exception->getMessage()
        ]);
    }
}
