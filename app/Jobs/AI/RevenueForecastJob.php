<?php

namespace App\Jobs\AI;

use App\Services\AI\RevenueForecastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RevenueForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300;
    public int $timeout = 900;

    public function __construct(
        public readonly string $forecastId,
        public readonly array $historicalData,
        public readonly int $periods = 12,
        public readonly ?string $modelId = null
    ) {}

    public function handle(RevenueForecastService $revenueService): void
    {
        Log::info("RevenueForecastJob: Generating revenue forecast {$this->forecastId}");

        try {
            $result = $revenueService->forecastRevenue(
                Uuid::fromString($this->forecastId),
                $this->historicalData,
                $this->periods,
                $this->modelId
            );

            Log::info("RevenueForecastJob: Revenue forecast completed", [
                'forecast_id' => $this->forecastId,
                'periods' => $this->periods,
                'predicted_total' => $result['predicted_total'] ?? null,
                'growth_rate' => $result['growth_rate'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("RevenueForecastJob: Revenue forecast failed", [
                'forecast_id' => $this->forecastId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("RevenueForecastJob: Revenue forecast permanently failed", [
            'forecast_id' => $this->forecastId,
            'error' => $exception->getMessage()
        ]);
    }
}
