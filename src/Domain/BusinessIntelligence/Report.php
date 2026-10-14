<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\Enums\ReportStatus;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Report extends AggregateRoot
{
    private ReportStatus $status;
    private array $sections = [];
    private array $filters = [];
    private array $parameters = [];
    private ?string $generatedFilePath = null;
    private ?DateTimeImmutable $generatedAt = null;
    private ?int $executionTime = null;
    private array $schedule = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $type, // pdf, excel, csv, html
        public readonly string $module,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {
        $this->status = ReportStatus::DRAFT;
    }

    public static function create(
        string $name,
        string $description,
        string $type,
        string $module,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            description: $description,
            type: $type,
            module: $module,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function addSection(string $title, array $content, ?string $chartType = null): void
    {
        $this->sections[] = [
            'title' => $title,
            'content' => $content,
            'chart_type' => $chartType,
            'order' => count($this->sections) + 1,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addFilter(FilterCriteria $filter): void
    {
        $this->filters[] = $filter->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsGenerating(): void
    {
        $this->status = ReportStatus::GENERATING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsReady(string $filePath, int $executionTime): void
    {
        $this->status = ReportStatus::READY;
        $this->generatedFilePath = $filePath;
        $this->generatedAt = new DateTimeImmutable();
        $this->executionTime = $executionTime;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = ReportStatus::FAILED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function schedule(string $cronExpression, array $recipients): void
    {
        $this->schedule = [
            'cron_expression' => $cronExpression,
            'recipients' => $recipients,
            'next_run' => $this->calculateNextRun($cronExpression),
        ];
        $this->status = ReportStatus::SCHEDULED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancelSchedule(): void
    {
        $this->schedule = [];
        $this->status = ReportStatus::DRAFT;
        $this->updatedAt = new DateTimeImmutable();
    }

    private function calculateNextRun(string $cronExpression): ?DateTimeImmutable
    {
        // Simplified cron parsing - in production use a proper cron library
        // This is just a placeholder
        return new DateTimeImmutable('+1 day');
    }

    public function getStatus(): ReportStatus
    {
        return $this->status;
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getGeneratedFilePath(): ?string
    {
        return $this->generatedFilePath;
    }

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'module' => $this->module,
            'status' => $this->status->value,
            'sections' => $this->sections,
            'filters' => $this->filters,
            'parameters' => $this->parameters,
            'generated_file_path' => $this->generatedFilePath,
            'generated_at' => $this->generatedAt?->format('Y-m-d H:i:s'),
            'execution_time' => $this->executionTime,
            'schedule' => $this->schedule,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
