<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\KPI;
use Src\Domain\BusinessIntelligence\Enums\KPIType;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\BusinessIntelligence\Enums\TrendDirection;
use Src\Domain\BusinessIntelligence\Repositories\KPIRepositoryInterface;
use Src\Domain\BusinessIntelligence\Events\KPIRecorded;
use Src\Domain\BusinessIntelligence\Events\KPIThresholdBreached;
use Src\Domain\BusinessIntelligence\ValueObjects\MetricValue;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class KPIService
{
    public function __construct(
        private readonly KPIRepositoryInterface $kpiRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function createKPI(
        KPIType $type,
        string $name,
        string $description,
        string $module,
        DataGranularity $granularity,
        ?Uuid $createdBy = null
    ): KPI {
        $kpi = KPI::create(
            type: $type,
            name: $name,
            description: $description,
            module: $module,
            granularity: $granularity,
            createdBy: $createdBy
        );

        $this->kpiRepository->save($kpi);

        return $kpi;
    }

    public function recordKPIValue(
        Uuid $kpiId,
        float $value,
        ?float $previousValue = null,
        ?float $targetValue = null,
        ?array $breakdown = null
    ): MetricValue {
        $kpi = $this->kpiRepository->findById($kpiId);

        if (!$kpi) {
            throw new \DomainException('KPI not found');
        }

        $kpi->recordValue(
            value: $value,
            unit: $kpi->type->getUnit(),
            breakdown: $breakdown
        );

        if ($previousValue !== null) {
            $kpi->compareWithPreviousPeriod($previousValue);
        }

        if ($targetValue !== null) {
            $kpi->setTarget($targetValue);
        }

        $this->kpiRepository->save($kpi);

        $metricValue = new MetricValue(
            metricName: $kpi->type->value,
            value: $value,
            unit: $kpi->type->getUnit(),
            previousValue: $previousValue,
            targetValue: $targetValue,
            breakdown: $breakdown
        );

        $this->eventDispatcher->dispatch(new KPIRecorded(
            kpiId: $kpi->id,
            kpiType: $kpi->type->value,
            value: $value,
            previousValue: $previousValue,
            targetValue: $targetValue
        ));

        // Check thresholds
        $this->checkThresholds($kpi, $value);

        return $metricValue;
    }

    public function setThresholds(Uuid $kpiId, float $warning, float $critical): void
    {
        $kpi = $this->kpiRepository->findById($kpiId);

        if (!$kpi) {
            throw new \DomainException('KPI not found');
        }

        $kpi->setThresholds($warning, $critical);
        $this->kpiRepository->save($kpi);
    }

    public function setTarget(Uuid $kpiId, float $target, ?\DateTimeImmutable $effectiveFrom = null): void
    {
        $kpi = $this->kpiRepository->findById($kpiId);

        if (!$kpi) {
            throw new \DomainException('KPI not found');
        }

        $kpi->setTarget($target, $effectiveFrom);
        $this->kpiRepository->save($kpi);
    }

    public function getKPIStatus(Uuid $kpiId): array
    {
        $kpi = $this->kpiRepository->findById($kpiId);

        if (!$kpi) {
            throw new \DomainException('KPI not found');
        }

        return [
            'id' => $kpi->id->toString(),
            'name' => $kpi->name,
            'type' => $kpi->type->value,
            'type_label' => $kpi->type->getLabel(),
            'category' => $kpi->type->getCategory(),
            'current_value' => $kpi->getCurrentValue(),
            'target' => $kpi->getCurrentTarget(),
            'status' => $kpi->getStatus(),
            'trend' => $kpi->getTrendDirection()->value,
            'thresholds' => $kpi->getThresholds(),
            'breakdown' => $kpi->getBreakdown(),
        ];
    }

    public function getKPIsByModule(string $module): array
    {
        return $this->kpiRepository->findByModule($module);
    }

    public function getKPIsByCategory(string $category): array
    {
        $kpis = $this->kpiRepository->findActiveKPIs();

        return array_filter($kpis, fn($kpi) => $kpi->type->getCategory() === $category);
    }

    public function calculateKPIsForPeriod(string $module, TimeRange $period): array
    {
        // This would calculate KPIs from actual data based on the module
        // Placeholder for the calculation logic
        $kpis = $this->kpiRepository->findByModule($module);
        $results = [];

        foreach ($kpis as $kpi) {
            // Calculate value based on KPI type and granularity
            $value = $this->calculateKPIValue($kpi, $period);
            $results[] = $this->recordKPIValue($kpi->id, $value);
        }

        return $results;
    }

    private function calculateKPIValue(KPI $kpi, TimeRange $period): float
    {
        // Placeholder - in real implementation this would query data based on KPI type
        return match($kpi->type) {
            KPIType::REVENUE => 0,
            KPIType::CUSTOMER_COUNT => 0,
            KPIType::CHURN_RATE => 0,
            default => 0,
        };
    }

    private function checkThresholds(KPI $kpi, float $value): void
    {
        $thresholds = $kpi->getThresholds();

        if (empty($thresholds)) {
            return;
        }

        if (isset($thresholds['critical']) && $value >= $thresholds['critical']) {
            $this->eventDispatcher->dispatch(new KPIThresholdBreached(
                kpiId: $kpi->id,
                kpiType: $kpi->type->value,
                value: $value,
                thresholdType: 'critical',
                thresholdValue: $thresholds['critical']
            ));
        } elseif (isset($thresholds['warning']) && $value >= $thresholds['warning']) {
            $this->eventDispatcher->dispatch(new KPIThresholdBreached(
                kpiId: $kpi->id,
                kpiType: $kpi->type->value,
                value: $value,
                thresholdType: 'warning',
                thresholdValue: $thresholds['warning']
            ));
        }
    }
}
