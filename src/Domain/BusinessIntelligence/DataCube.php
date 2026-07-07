<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\ValueObjects\DataCubeDefinition;
use Src\Domain\BusinessIntelligence\ValueObjects\AggregationRule;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class DataCube extends AggregateRoot
{
    private array $measures = [];
    private array $dimensions = [];
    private array $filters = [];
    private array $preAggregations = [];
    private ?array $lastRefreshedData = null;
    private ?DateTimeImmutable $lastRefreshedAt = null;
    private array $statistics = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $table,
        public readonly string $description,
        public readonly string $module,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $name,
        string $table,
        string $description,
        string $module,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            table: $table,
            description: $description,
            module: $module,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function fromDefinition(DataCubeDefinition $definition, ?Uuid $createdBy = null): self
    {
        $cube = new self(
            id: Uuid::generate(),
            name: $definition->name,
            table: $definition->table,
            description: $definition->description ?? '',
            module: 'general',
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );

        foreach ($definition->measures as $measure) {
            $cube->addMeasure($measure);
        }

        foreach ($definition->dimensions as $dimension) {
            $cube->addDimension($dimension);
        }

        return $cube;
    }

    public function addMeasure(AggregationRule $measure): void
    {
        $this->measures[] = $measure->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addDimension(string $dimension, ?string $label = null, ?string $dataType = null): void
    {
        $this->dimensions[] = [
            'name' => $dimension,
            'label' => $label ?? $dimension,
            'data_type' => $dataType ?? 'string',
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addFilter(FilterCriteria $filter): void
    {
        $this->filters[] = $filter->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addPreAggregation(string $name, array $dimensions, array $measures): void
    {
        $this->preAggregations[] = [
            'name' => $name,
            'dimensions' => $dimensions,
            'measures' => $measures,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsRefreshed(array $data): void
    {
        $this->lastRefreshedData = $data;
        $this->lastRefreshedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setStatistics(array $statistics): void
    {
        $this->statistics = $statistics;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMeasures(): array
    {
        return $this->measures;
    }

    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getPreAggregations(): array
    {
        return $this->preAggregations;
    }

    public function getLastRefreshedData(): ?array
    {
        return $this->lastRefreshedData;
    }

    public function getLastRefreshedAt(): ?DateTimeImmutable
    {
        return $this->lastRefreshedAt;
    }

    public function getStatistics(): array
    {
        return $this->statistics;
    }

    public function getMeasureNames(): array
    {
        return array_map(fn($m) => $m['alias'] ?? $m['field'], $this->measures);
    }

    public function getDimensionNames(): array
    {
        return array_map(fn($d) => $d['name'], $this->dimensions);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'table' => $this->table,
            'description' => $this->description,
            'module' => $this->module,
            'measures' => $this->measures,
            'dimensions' => $this->dimensions,
            'filters' => $this->filters,
            'pre_aggregations' => $this->preAggregations,
            'last_refreshed_data' => $this->lastRefreshedData,
            'last_refreshed_at' => $this->lastRefreshedAt?->format('Y-m-d H:i:s'),
            'statistics' => $this->statistics,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
