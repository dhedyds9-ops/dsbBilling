<?php

namespace App\Jobs\BusinessIntelligence;

use App\Services\BusinessIntelligence\ReportingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReportGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 600;

    public function __construct(
        public readonly string $reportId,
        public readonly array $parameters = []
    ) {}

    public function handle(ReportingService $reportingService): void
    {
        Log::info("ReportGenerationJob: Generating report {$this->reportId}");

        try {
            $report = $reportingService->generateReport(
                \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->reportId),
                $this->parameters
            );

            Log::info("ReportGenerationJob: Report {$this->reportId} generated successfully", [
                'file_path' => $report->getGeneratedFilePath(),
                'execution_time' => $report->getExecutionTime()
            ]);

        } catch (\Exception $e) {
            Log::error("ReportGenerationJob: Failed to generate report {$this->reportId}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ReportGenerationJob: Report {$this->reportId} failed permanently", [
            'error' => $exception->getMessage()
        ]);
    }
}
