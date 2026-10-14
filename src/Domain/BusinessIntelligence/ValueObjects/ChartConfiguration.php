<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

class ChartConfiguration
{
    public const TYPE_LINE = 'line';
    public const TYPE_BAR = 'bar';
    public const TYPE_PIE = 'pie';
    public const TYPE_DOUGHNUT = 'doughnut';
    public const TYPE_AREA = 'area';
    public const TYPE_SCATTER = 'scatter';
    public const TYPE_GAUGE = 'gauge';
    public const TYPE_NUMBER = 'number';
    public const TYPE_TABLE = 'table';
    public const TYPE_HEATMAP = 'heatmap';

    public function __construct(
        public readonly string $type,
        public readonly array $options = [],
        public readonly ?array $colors = null,
        public readonly ?array $labels = null,
        public readonly ?array $dataSource = null
    ) {}

    public static function line(array $options = []): self
    {
        return new self(self::TYPE_LINE, array_merge([
            'showLegend' => true,
            'showGrid' => true,
            'smooth' => true,
        ], $options));
    }

    public static function bar(array $options = []): self
    {
        return new self(self::TYPE_BAR, array_merge([
            'showLegend' => true,
            'showGrid' => true,
            'horizontal' => false,
        ], $options));
    }

    public static function pie(array $options = []): self
    {
        return new self(self::TYPE_PIE, array_merge([
            'showLegend' => true,
            'showLabels' => true,
        ], $options));
    }

    public static function doughnut(array $options = []): self
    {
        return new self(self::TYPE_DOUGHNUT, array_merge([
            'showLegend' => true,
            'showLabels' => true,
        ], $options));
    }

    public static function area(array $options = []): self
    {
        return new self(self::TYPE_AREA, array_merge([
            'showLegend' => true,
            'showGrid' => true,
            'smooth' => true,
        ], $options));
    }

    public static function gauge(float $min = 0, float $max = 100, array $options = []): self
    {
        return new self(self::TYPE_GAUGE, array_merge([
            'min' => $min,
            'max' => $max,
            'showLabels' => true,
        ], $options));
    }

    public static function number(array $options = []): self
    {
        return new self(self::TYPE_NUMBER, array_merge([
            'showTrend' => true,
            'showComparison' => true,
            'prefix' => '',
            'suffix' => '',
        ], $options));
    }

    public static function table(array $options = []): self
    {
        return new self(self::TYPE_TABLE, array_merge([
            'sortable' => true,
            'filterable' => true,
            'pageable' => true,
            'pageSize' => 10,
        ], $options));
    }

    public static function heatmap(array $options = []): self
    {
        return new self(self::TYPE_HEATMAP, array_merge([
            'cellSize' => 20,
            'colorScale' => 'default',
        ], $options));
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'options' => $this->options,
            'colors' => $this->colors,
            'labels' => $this->labels,
            'data_source' => $this->dataSource,
        ];
    }
}
