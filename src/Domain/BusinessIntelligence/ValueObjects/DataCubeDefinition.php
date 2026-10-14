<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

use DateTimeImmutable;

class DataCubeDefinition
{
    /**
     * @param AggregationRule[] $measures
     * @param string[] $dimensions
     */
    public function __construct(
        public readonly string $name,
        public readonly string $table,
        public readonly array $measures,
        public readonly array $dimensions,
        public readonly ?array $filters = null,
        public readonly ?string $description = null
    ) {}

    public function addMeasure(AggregationRule $measure): self
    {
        return new self(
            $this->name,
            $this->table,
            array_merge($this->measures, [$measure]),
            $this->dimensions,
            $this->filters,
            $this->description
        );
    }

    public function addDimension(string $dimension): self
    {
        return new self(
            $this->name,
            $this->table,
            $this->measures,
            array_merge($this->dimensions, [$dimension]),
            $this->filters,
            $this->description
        );
    }

    public function withFilters(array $filters): self
    {
        return new self(
            $this->name,
            $this->table,
            $this->measures,
            $this->dimensions,
            $filters,
            $this->description
        );
    }

    public function getMeasureNames(): array
    {
        return array_map(fn($m) => $m->getAlias(), $this->measures);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'table' => $this->table,
            'measures' => array_map(fn($m) => $m->toArray(), $this->measures),
            'dimensions' => $this->dimensions,
            'filters' => $this->filters,
            'description' => $this->description,
        ];
    }

    public static function revenue(): self
    {
        return new self(
            'revenue_cube',
            'invoices',
            [
                AggregationRule::sum('total_amount', 'total_revenue'),
                AggregationRule::sum('paid_amount', 'total_paid'),
                AggregationRule::avg('total_amount', 'average_revenue'),
                AggregationRule::count('id', 'transaction_count'),
            ],
            ['billing_cycle_id', 'customer_id', 'status', 'created_at'],
            null,
            'Revenue analytics data cube'
        );
    }

    public static function customer(): self
    {
        return new self(
            'customer_cube',
            'customers',
            [
                AggregationRule::count('id', 'total_customers'),
                AggregationRule::countDistinct('id', 'unique_customers'),
            ],
            ['status', 'package_id', 'area_id', 'created_at'],
            null,
            'Customer analytics data cube'
        );
    }

    public static function network(): self
    {
        return new self(
            'network_cube',
            'network_devices',
            [
                AggregationRule::count('id', 'total_devices'),
                AggregationRule::countDistinct('id', 'unique_devices'),
            ],
            ['type', 'status', 'olt_id', 'area_id'],
            null,
            'Network equipment analytics data cube'
        );
    }
}
