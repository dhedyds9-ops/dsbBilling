<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\Enums\WidgetType;
use Src\Domain\BusinessIntelligence\ValueObjects\ChartConfiguration;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\BusinessIntelligence\ValueObjects\AggregationRule;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Widget extends AggregateRoot
{
    private array $dataSource;
    private array $dimensions = [];
    private array $measures = [];
    private array $filters = [];
    private ?ChartConfiguration $chartConfig = null;
    private array $formatting = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly WidgetType $type,
        public readonly ?Uuid $dashboardId = null,
        public readonly ?Uuid $createdBy = null,
        public readonly int $refreshInterval = 300, // seconds
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {
        $this->dataSource = [];
    }

    public static function create(
        string $name,
        WidgetType $type,
        ?Uuid $dashboardId = null,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            type: $type,
            dashboardId: $dashboardId,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function setDataSource(string $table, array $fields): void
    {
        $this->dataSource = [
            'table' => $table,
            'fields' => $fields,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addDimension(string $field, ?string $alias = null): void
    {
        $this->dimensions[] = [
            'field' => $field,
            'alias' => $alias ?? $field,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addMeasure(AggregationRule $measure): void
    {
        $this->measures[] = $measure->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addFilter(FilterCriteria $filter): void
    {
        $this->filters[] = $filter->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setChartConfiguration(ChartConfiguration $config): void
    {
        $this->chartConfig = $config;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setFormatting(array $formatting): void
    {
        $this->formatting = $formatting;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setRefreshInterval(int $seconds): void
    {
        $this->refreshInterval = $seconds;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDataSource(): array
    {
        return $this->dataSource;
    }

    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    public function getMeasures(): array
    {
        return $this->measures;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getChartConfiguration(): ?ChartConfiguration
    {
        return $this->chartConfig;
    }

    public function getFormatting(): array
    {
        return $this->formatting;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'type' => $this->type->value,
            'dashboard_id' => $this->dashboardId?->toString(),
            'data_source' => $this->dataSource,
            'dimensions' => $this->dimensions,
            'measures' => $this->measures,
            'filters' => $this->filters,
            'chart_configuration' => $this->chartConfig?->toArray(),
            'formatting' => $this->formatting,
            'refresh_interval' => $this->refreshInterval,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
