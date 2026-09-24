<?php

namespace App\Jobs\BusinessIntelligence;

use App\Models\BusinessIntelligence\BIReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

class ScheduledReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 600;

    public function __construct(
        public readonly string $reportId
    ) {}

    public function handle(ReportingService $reportingService): void
    {
        Log::info("ScheduledReportJob: Processing scheduled report {$this->reportId}");

        try {
            $report = BIReport::find($this->reportId);

            if (!$report || $report->status !== 'scheduled') {
                Log::warning("ScheduledReportJob: Report {$this->reportId} not found or not scheduled");
                return;
            }

            // Generate the report
            $generatedReport = $reportingService->generateReport(
                \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->reportId)
            );

            // Send to recipients
            $this->sendReportToRecipients($generatedReport);

            Log::info("ScheduledReportJob: Scheduled report {$this->reportId} processed successfully");

        } catch (\Exception $e) {
            Log::error("ScheduledReportJob: Failed to process scheduled report {$this->reportId}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function sendReportToRecipients($report): void
    {
        $recipients = $report->getRecipients();
        $filePath = $report->getGeneratedFilePath();

        foreach ($recipients as $recipient) {
            // In real implementation, would send email with attachment
            Log::info("ScheduledReportJob: Sending report to {$recipient}", [
                'file_path' => $filePath
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ScheduledReportJob: Scheduled report {$this->reportId} failed permanently", [
            'error' => $exception->getMessage()
        ]);
    }
}
