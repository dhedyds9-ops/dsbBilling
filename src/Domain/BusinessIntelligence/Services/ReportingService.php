<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\Report;
use Src\Domain\BusinessIntelligence\Enums\ReportStatus;
use Src\Domain\BusinessIntelligence\Repositories\ReportRepositoryInterface;
use Src\Domain\BusinessIntelligence\Events\ReportGenerated;
use Src\Domain\BusinessIntelligence\Events\ReportScheduled;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class ReportingService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function createReport(
        string $name,
        string $description,
        string $type,
        string $module,
        ?Uuid $createdBy = null
    ): Report {
        $report = Report::create(
            name: $name,
            description: $description,
            type: $type,
            module: $module,
            createdBy: $createdBy
        );

        $this->reportRepository->save($report);

        return $report;
    }

    public function addSection(Uuid $reportId, string $title, array $content, ?string $chartType = null): Report
    {
        $report = $this->reportRepository->findById($reportId);

        if (!$report) {
            throw new \DomainException('Report not found');
        }

        $report->addSection($title, $content, $chartType);
        $this->reportRepository->save($report);

        return $report;
    }

    public function generateReport(Uuid $reportId, array $parameters = []): Report
    {
        $report = $this->reportRepository->findById($reportId);

        if (!$report) {
            throw new \DomainException('Report not found');
        }

        $report->markAsGenerating();

        foreach ($parameters as $key => $value) {
            $report->setParameter($key, $value);
        }

        $this->reportRepository->save($report);

        $startTime = microtime(true);

        try {
            // Generate the report content
            $content = $this->executeReportGeneration($report);

            // Save the generated file
            $filePath = $this->saveReportFile($report, $content);

            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            $report->markAsReady($filePath, $executionTime);
            $this->reportRepository->save($report);

            $this->eventDispatcher->dispatch(new ReportGenerated(
                reportId: $report->id,
                reportName: $report->name,
                filePath: $filePath,
                executionTimeMs: $executionTime
            ));

        } catch (\Exception $e) {
            $report->markAsFailed();
            $this->reportRepository->save($report);
            throw $e;
        }

        return $report;
    }

    public function scheduleReport(
        Uuid $reportId,
        string $cronExpression,
        array $recipients
    ): Report {
        $report = $this->reportRepository->findById($reportId);

        if (!$report) {
            throw new \DomainException('Report not found');
        }

        $report->schedule($cronExpression, $recipients);
        $this->reportRepository->save($report);

        $this->eventDispatcher->dispatch(new ReportScheduled(
            reportId: $report->id,
            cronExpression: $cronExpression,
            recipients: $recipients
        ));

        return $report;
    }

    public function cancelSchedule(Uuid $reportId): Report
    {
        $report = $this->reportRepository->findById($reportId);

        if (!$report) {
            throw new \DomainException('Report not found');
        }

        $report->cancelSchedule();
        $this->reportRepository->save($report);

        return $report;
    }

    public function getReportStatus(Uuid $reportId): array
    {
        $report = $this->reportRepository->findById($reportId);

        if (!$report) {
            throw new \DomainException('Report not found');
        }

        return [
            'id' => $report->id->toString(),
            'name' => $report->name,
            'status' => $report->getStatus()->value,
            'generated_file_path' => $report->getGeneratedFilePath(),
            'generated_at' => $report->getGeneratedFilePath() ? now()->format('Y-m-d H:i:s') : null,
            'execution_time' => $report->getExecutionTime(),
            'schedule' => $report->getSchedule(),
        ];
    }

    public function getReportsByModule(string $module): array
    {
        return $this->reportRepository->findByModule($module);
    }

    public function getScheduledReports(): array
    {
        return $this->reportRepository->findScheduledReports();
    }

    public function getRecentReports(int $limit = 10): array
    {
        return $this->reportRepository->findRecentReports($limit);
    }

    private function executeReportGeneration(Report $report): array
    {
        // Placeholder - in real implementation this would generate the actual report
        return [
            'sections' => $report->getSections(),
            'parameters' => $report->getParameters(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function saveReportFile(Report $report, array $content): string
    {
        $filename = sprintf(
            '%s_%s_%s.%s',
            $report->name,
            now()->format('Ymd_His'),
            Uuid::generate()->toString()[:8],
            $report->type
        );

        $path = storage_path("app/reports/{$filename}");

        // In real implementation, would generate actual file based on type
        // For now, return placeholder path
        return $path;
    }
}
