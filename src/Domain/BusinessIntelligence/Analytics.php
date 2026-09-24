<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\ValueObjects\FilterCriteria;
use Src\Domain\BusinessIntelligence\ValueObjects\AggregationRule;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Analytics extends AggregateRoot
{
    private array $metrics = [];
    private array $dimensions = [];
    private array $segments = [];
    private ?TimeRange $timeRange = null;
    private array $comparisons = [];
    private array $metadata = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $type, // revenue, customer, network, service
        public readonly string $module,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $name,
        string $type,
        string $module,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            type: $type,
            module: $module,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function setTimeRange(TimeRange $timeRange): void
    {
        $this->timeRange = $timeRange;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addMetric(string $name, AggregationRule $aggregation, ?string $label = null): void
    {
        $this->metrics[] = [
            'name' => $name,
            'aggregation' => $aggregation->toArray(),
            'label' => $label ?? $name,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addDimension(string $field, ?string $label = null): void
    {
        $this->dimensions[] = [
            'field' => $field,
            'label' => $label ?? $field,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addSegment(string $name, array $conditions): void
    {
        $this->segments[] = [
            'name' => $name,
            'conditions' => $conditions,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addComparison(string $name, TimeRange $timeRange, ?string $label = null): void
    {
        $this->comparisons[] = [
            'name' => $name,
            'time_range' => $timeRange->toArray(),
            'label' => $label ?? $name,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getTimeRange(): ?TimeRange
    {
        return $this->timeRange;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    public function getComparisons(): array
    {
        return $this->comparisons;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'type' => $this->type,
            'module' => $this->module,
            'time_range' => $this->timeRange?->toArray(),
            'metrics' => $this->metrics,
            'dimensions' => $this->dimensions,
            'segments' => $this->segments,
            'comparisons' => $this->comparisons,
            'metadata' => $this->metadata,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
