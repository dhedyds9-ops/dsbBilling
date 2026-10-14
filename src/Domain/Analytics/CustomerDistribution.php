<?php

namespace Src\Domain\Analytics;

use DateTimeImmutable;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\Analytics\ValueObjects\DensityMetric;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CustomerDistribution extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $areaId,
        public readonly string $areaName,
        public AnalyticsType $type,
        public TimeRange $timeRange,
        public DateTimeImmutable $generatedAt,
        public DensityMetric $densityMetric,
        public int $totalCustomers,
        public int $activeCustomers,
        public int $inactiveCustomers,
        public int $newCustomers,
        public int $churnedCustomers,
        public array $distributionByZone = [],
        public array $distributionByServiceType = [],
        public array $densityByGrid = [],
        public array $hotspots = [],
        public array $coldspots = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        string $areaId,
        string $areaName,
        DensityMetric $densityMetric,
        int $totalCustomers,
        int $activeCustomers
    ): self {
        return new self(
            $id,
            $areaId,
            $areaName,
            AnalyticsType::CUSTOMER_DENSITY,
            TimeRange::MONTH,
            new DateTimeImmutable(),
            $densityMetric,
            $totalCustomers,
            $activeCustomers,
            $totalCustomers - $activeCustomers,
            0,
            0
        );
    }

    public function setNewCustomers(int $count): void
    {
        $this->newCustomers = $count;
    }

    public function setChurnedCustomers(int $count): void
    {
        $this->churnedCustomers = $count;
    }

    public function setDistributionByZone(array $data): void
    {
        $this->distributionByZone = $data;
    }

    public function setDistributionByServiceType(array $data): void
    {
        $this->distributionByServiceType = $data;
    }

    public function setDensityByGrid(array $data): void
    {
        $this->densityByGrid = $data;
    }

    public function identifyHotspots(int $threshold = 100): void
    {
        $this->hotspots = array_filter(
            $this->densityByGrid,
            fn($grid) => ($grid['customer_count'] ?? 0) >= $threshold
        );
    }

    public function identifyColdspots(int $threshold = 10): void
    {
        $this->coldspots = array_filter(
            $this->densityByGrid,
            fn($grid) => ($grid['customer_count'] ?? 0) <= $threshold
        );
    }

    public function getNetGrowth(): int
    {
        return $this->newCustomers - $this->churnedCustomers;
    }

    public function getGrowthRate(): float
    {
        if ($this->totalCustomers === 0) {
            return 0.0;
        }
        return ($this->getNetGrowth() / $this->totalCustomers) * 100;
    }

    public function getActivationRate(): float
    {
        return $this->totalCustomers > 0 
            ? ($this->activeCustomers / $this->totalCustomers) * 100 
            : 0.0;
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
            'type' => $this->type->value,
            'time_range' => $this->timeRange->value,
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
            'density_metric' => $this->densityMetric->toArray(),
            'total_customers' => $this->totalCustomers,
            'active_customers' => $this->activeCustomers,
            'inactive_customers' => $this->inactiveCustomers,
            'new_customers' => $this->newCustomers,
            'churned_customers' => $this->churnedCustomers,
            'net_growth' => $this->getNetGrowth(),
            'growth_rate' => round($this->getGrowthRate(), 2),
            'activation_rate' => round($this->getActivationRate(), 2),
            'distribution_by_zone' => $this->distributionByZone,
            'distribution_by_service_type' => $this->distributionByServiceType,
            'density_by_grid' => $this->densityByGrid,
            'hotspots' => array_values($this->hotspots),
            'coldspots' => array_values($this->coldspots),
            'metadata' => $this->metadata,
        ];
    }
}
