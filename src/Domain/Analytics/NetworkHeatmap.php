<?php

namespace Src\Domain\Analytics;

use DateTimeImmutable;
use Src\Domain\Analytics\Enums\HeatmapLayer;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\Events\HeatmapUpdated;
use Src\Domain\Analytics\ValueObjects\GeoCoordinate;
use Src\Domain\Analytics\ValueObjects\HeatmapCell;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class NetworkHeatmap extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $areaId,
        public readonly string $areaName,
        public HeatmapLayer $layer,
        public TimeRange $timeRange,
        public DateTimeImmutable $generatedAt,
        public float $minLat,
        public float $maxLat,
        public float $minLon,
        public float $maxLon,
        public float $gridSizeKm,
        public array $cells = [],
        public int $totalCustomers = 0,
        public float $maxValue = 0.0,
        public float $minValue = 0.0,
        public float $averageValue = 0.0,
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $areaId,
        string $areaName,
        HeatmapLayer $layer,
        GeoCoordinate $center,
        float $radiusKm,
        float $gridSizeKm = 0.5
    ): self {
        $bbox = $center->toBoundingBox($radiusKm);

        return new self(
            $id,
            $areaId,
            $areaName,
            $layer,
            TimeRange::DAY,
            new DateTimeImmutable(),
            $bbox['min_lat'],
            $bbox['max_lat'],
            $bbox['min_lon'],
            $bbox['max_lon'],
            $gridSizeKm
        );
    }

    public function setCells(array $cells): void
    {
        $this->cells = array_map(
            fn($cell) => $cell instanceof HeatmapCell ? $cell : HeatmapCell::fromArray($cell),
            $cells
        );
        
        $this->recalculateStatistics();
    }

    public function addCell(HeatmapCell $cell): void
    {
        $this->cells[] = $cell;
        $this->recalculateStatistics();
    }

    private function recalculateStatistics(): void
    {
        if (empty($this->cells)) {
            $this->maxValue = 0;
            $this->minValue = 0;
            $this->averageValue = 0;
            return;
        }

        $values = array_column(array_map(fn($c) => $c->toArray(), $this->cells), 'value');
        $this->maxValue = max($values);
        $this->minValue = min($values);
        $this->averageValue = array_sum($values) / count($values);
        $this->totalCustomers = array_sum(array_column(array_map(fn($c) => $c->toArray(), $this->cells), 'customer_count'));
    }

    public function getCellCount(): int
    {
        return count($this->cells);
    }

    public function getCellsInRadius(GeoCoordinate $center, float $radiusKm): array
    {
        return array_filter(
            $this->cells,
            fn($cell) => (new GeoCoordinate($cell->centerLat, $cell->centerLon))->distanceTo($center) <= $radiusKm
        );
    }

    public function getCriticalCells(float $threshold = 90): array
    {
        return array_filter(
            $this->cells,
            fn($cell) => $cell->value >= $threshold
        );
    }

    public function getWarningCells(float $minThreshold = 75, float $maxThreshold = 90): array
    {
        return array_filter(
            $this->cells,
            fn($cell) => $cell->value >= $minThreshold && $cell->value < $maxThreshold
        );
    }

    public function getColorForValue(float $value): string
    {
        if ($value >= 90) {
            return '#ff0000';
        } elseif ($value >= 75) {
            return '#ffff00';
        } elseif ($value >= 50) {
            return '#00ff00';
        }
        return '#00ff00';
    }

    public function toGeoJson(): array
    {
        $features = [];

        foreach ($this->cells as $cell) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$cell->centerLon, $cell->centerLat]
                ],
                'properties' => $cell->toArray()
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }

    public function fireUpdateEvent(): void
    {
        $this->recordThat(new HeatmapUpdated(
            $this->id->value,
            $this->layer,
            $this->areaId,
            count($this->cells),
            $this->totalCustomers,
            $this->maxValue,
            $this->minValue
        ));
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'area_id' => $this->areaId,
            'area_name' => $this->areaName,
            'layer' => $this->layer->value,
            'time_range' => $this->timeRange->value,
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
            'bounds' => [
                'min_lat' => round($this->minLat, 6),
                'max_lat' => round($this->maxLat, 6),
                'min_lon' => round($this->minLon, 6),
                'max_lon' => round($this->maxLon, 6),
            ],
            'grid_size_km' => $this->gridSizeKm,
            'cell_count' => count($this->cells),
            'total_customers' => $this->totalCustomers,
            'max_value' => round($this->maxValue, 2),
            'min_value' => round($this->minValue, 2),
            'average_value' => round($this->averageValue, 2),
            'cells' => array_map(fn($c) => $c->toArray(), $this->cells),
            'metadata' => $this->metadata,
        ];
    }
}
