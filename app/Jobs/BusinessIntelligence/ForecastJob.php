<?php

namespace App\Jobs\BusinessIntelligence;

use App\Services\BusinessIntelligence\ForecastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 600;

    public function __construct(
        public readonly string $forecastId,
        public readonly array $historicalData,
        public readonly string $startDate,
        public readonly string $endDate
    ) {}

    public function handle(ForecastService $forecastService): void
    {
        Log::info("ForecastJob: Generating forecast {$this->forecastId}");

        try {
            $timeRange = \Src\Domain\BusinessIntelligence\ValueObjects\TimeRange::create(
                startDate: new \DateTimeImmutable($this->startDate),
                endDate: new \DateTimeImmutable($this->endDate)
            );

            $result = $forecastService->generateForecast(
                \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->forecastId),
                $this->historicalData,
                $timeRange
            );

            Log::info("ForecastJob: Completed forecast {$this->forecastId}", [
                'predictions_count' => count($result->predictions),
                'accuracy' => $result->accuracy
            ]);

        } catch (\Exception $e) {
            Log::error("ForecastJob: Failed to generate forecast {$this->forecastId}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ForecastJob: Forecast {$this->forecastId} failed permanently", [
            'error' => $exception->getMessage()
        ]);
    }
}
