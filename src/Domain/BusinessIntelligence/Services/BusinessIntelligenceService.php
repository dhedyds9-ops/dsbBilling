<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\Dashboard;
use Src\Domain\BusinessIntelligence\KPI;
use Src\Domain\BusinessIntelligence\Report;
use Src\Domain\BusinessIntelligence\Analytics;
use Src\Domain\BusinessIntelligence\Forecast;
use Src\Domain\BusinessIntelligence\DataCube;
use Src\Domain\BusinessIntelligence\Repositories\DashboardRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\KPIRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\ReportRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\AnalyticsRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\ForecastRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\DataCubeRepositoryInterface;
use Src\Domain\BusinessIntelligence\ValueObjects\TimeRange;
use Src\Domain\BusinessIntelligence\Enums\KPIType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class BusinessIntelligenceService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
        private readonly KPIRepositoryInterface $kpiRepository,
        private readonly ReportRepositoryInterface $reportRepository,
        private readonly AnalyticsRepositoryInterface $analyticsRepository,
        private readonly ForecastRepositoryInterface $forecastRepository,
        private readonly DataCubeRepositoryInterface $dataCubeRepository,
        private readonly DashboardService $dashboardService,
        private readonly KPIService $kpiService,
        private readonly AnalyticsService $analyticsService,
        private readonly ForecastService $forecastService,
        private readonly ReportingService $reportingService
    ) {}

    /**
     * Get Executive Dashboard Data
     * Returns comprehensive overview for executive decision making
     */
    public function getExecutiveDashboardData(): array
    {
        $kpis = $this->kpiRepository->findActiveKPIs();

        $revenueKPIs = $this->filterKPIsByCategory($kpis, 'Revenue');
        $customerKPIs = $this->filterKPIsByCategory($kpis, 'Customer');
        $networkKPIs = $this->filterKPIsByCategory($kpis, 'Network');
        $financialKPIs = $this->filterKPIsByCategory($kpis, 'Financial');

        $recentReports = $this->reportRepository->findRecentReports(5);
        $recentForecasts = $this->forecastRepository->findLatestForecasts(5);

        return [
            'summary' => [
                'total_active_kpis' => count($kpis),
                'total_dashboards' => count($this->dashboardRepository->findByStatus(
                    \Src\Domain\BusinessIntelligence\Enums\DashboardStatus::PUBLISHED
                )),
                'total_reports' => count($this->reportRepository->findByStatus(
                    \Src\Domain\BusinessIntelligence\Enums\ReportStatus::READY
                )),
            ],
            'revenue' => $this->formatKPIArray($revenueKPIs),
            'customer' => $this->formatKPIArray($customerKPIs),
            'network' => $this->formatKPIArray($networkKPIs),
            'financial' => $this->formatKPIArray($financialKPIs),
            'recent_reports' => array_map(fn($r) => $r->toArray(), $recentReports),
            'recent_forecasts' => array_map(fn($f) => $f->toArray(), $recentForecasts),
        ];
    }

    /**
     * Get Trend Analysis for a specific metric
     */
    public function getTrendAnalysis(string $metricName, TimeRange $period, string $granularity = 'daily'): array
    {
        return $this->analyticsService->getTrendAnalysis($metricName, $period, $granularity);
    }

    /**
     * Get Growth Prediction for a specific metric
     */
    public function getGrowthPrediction(
        string $metricName,
        TimeRange $historicalPeriod,
        int $futurePeriods = 12
    ): array {
        return $this->analyticsService->getGrowthPrediction($metricName, $historicalPeriod, $futurePeriods);
    }

    /**
     * Get Comparative Analysis
     */
    public function getComparativeAnalysis(
        string $metricName,
        TimeRange $currentPeriod,
        TimeRange $comparisonPeriod
    ): array {
        return $this->analyticsService->getComparativeAnalysis($metricName, $currentPeriod, $comparisonPeriod);
    }

    /**
     * Calculate Revenue Projections
     */
    public function getRevenueProjection(TimeRange $projectionPeriod): array
    {
        $revenueKPI = $this->kpiRepository->findByType(KPIType::REVENUE);
        $arpuKPI = $this->kpiRepository->findByType(KPIType::ARPU);
        $mrrKPI = $this->kpiRepository->findByType(KPIType::MRR);

        $currentRevenue = $revenueKPI?->getCurrentValue() ?? 0;
        $currentARPU = $arpuKPI?->getCurrentValue() ?? 0;
        $currentMRR = $mrrKPI?->getCurrentValue() ?? 0;

        // Get growth rate from forecast
        $revenueForecasts = $this->forecastRepository->findByMetricName('revenue');
        $growthRate = !empty($revenueForecasts)
            ? ($revenueForecasts[0]->getResult()?->getTrend() === 'increasing' ? 0.05 : -0.02)
            : 0.03;

        $projections = [];
        $periods = $projectionPeriod->getMonths();

        for ($i = 0; $i < $periods; $i++) {
            $projectedRevenue = $currentRevenue * pow(1 + $growthRate, $i);
            $projectedARPU = $currentARPU * pow(1 + $growthRate * 0.8, $i);
            $projectedMRR = $currentMRR * pow(1 + $growthRate, $i);

            $projections[] = [
                'month' => $i + 1,
                'projected_revenue' => round($projectedRevenue, 2),
                'projected_arpu' => round($projectedARPU, 2),
                'projected_mrr' => round($projectedMRR, 2),
            ];
        }

        return [
            'current_revenue' => $currentRevenue,
            'current_arpu' => $currentARPU,
            'current_mrr' => $currentMRR,
            'growth_rate' => $growthRate * 100,
            'projection_period_months' => $periods,
            'projections' => $projections,
        ];
    }

    /**
     * Calculate Capacity Planning metrics
     */
    public function getCapacityPlanningMetrics(): array
    {
        $oltCapacityKPI = $this->kpiRepository->findByType(KPIType::OLT_CAPACITY);
        $odpCapacityKPI = $this->kpiRepository->findByType(KPIType::ODP_CAPACITY);
        $bandwidthUsageKPI = $this->kpiRepository->findByType(KPIType::BANDWIDTH_USAGE);
        $fiberUtilizationKPI = $this->kpiRepository->findByType(KPIType::FIBER_UTILIZATION);

        return [
            'olt_capacity' => [
                'current' => $oltCapacityKPI?->getCurrentValue() ?? 0,
                'status' => $oltCapacityKPI?->getStatus() ?? 'unknown',
                'threshold_warning' => $oltCapacityKPI?->getThresholds()['warning'] ?? 75,
                'threshold_critical' => $oltCapacityKPI?->getThresholds()['critical'] ?? 90,
            ],
            'odp_capacity' => [
                'current' => $odpCapacityKPI?->getCurrentValue() ?? 0,
                'status' => $odpCapacityKPI?->getStatus() ?? 'unknown',
                'threshold_warning' => $odpCapacityKPI?->getThresholds()['warning'] ?? 75,
                'threshold_critical' => $odpCapacityKPI?->getThresholds()['critical'] ?? 90,
            ],
            'bandwidth_usage' => [
                'current' => $bandwidthUsageKPI?->getCurrentValue() ?? 0,
                'status' => $bandwidthUsageKPI?->getStatus() ?? 'unknown',
            ],
            'fiber_utilization' => [
                'current' => $fiberUtilizationKPI?->getCurrentValue() ?? 0,
                'status' => $fiberUtilizationKPI?->getStatus() ?? 'unknown',
            ],
        ];
    }

    /**
     * Get Customer Analytics Summary
     */
    public function getCustomerAnalyticsSummary(): array
    {
        $customerCountKPI = $this->kpiRepository->findByType(KPIType::CUSTOMER_COUNT);
        $customerGrowthKPI = $this->kpiRepository->findByType(KPIType::CUSTOMER_GROWTH);
        $churnRateKPI = $this->kpiRepository->findByType(KPIType::CHURN_RATE);

        return [
            'total_customers' => $customerCountKPI?->getCurrentValue() ?? 0,
            'customer_growth' => [
                'value' => $customerGrowthKPI?->getCurrentValue() ?? 0,
                'trend' => $customerGrowthKPI?->getTrendDirection()->value ?? 'stable',
                'status' => $customerGrowthKPI?->getStatus() ?? 'neutral',
            ],
            'churn_rate' => [
                'value' => $churnRateKPI?->getCurrentValue() ?? 0,
                'status' => $churnRateKPI?->getStatus() ?? 'neutral',
            ],
        ];
    }

    /**
     * Get Financial Summary
     */
    public function getFinancialSummary(): array
    {
        $revenueKPI = $this->kpiRepository->findByType(KPIType::REVENUE);
        $profitKPI = $this->kpiRepository->findByType(KPIType::PROFIT);
        $outstandingKPI = $this->kpiRepository->findByType(KPIType::OUTSTANDING_INVOICE);
        $collectionRateKPI = $this->kpiRepository->findByType(KPIType::COLLECTION_RATE);

        return [
            'revenue' => $revenueKPI?->getCurrentValue() ?? 0,
            'profit' => $profitKPI?->getCurrentValue() ?? 0,
            'profit_margin' => $revenueKPI?->getCurrentValue() != 0 && $revenueKPI?->getCurrentValue() > 0
                ? round(($profitKPI?->getCurrentValue() ?? 0) / $revenueKPI->getCurrentValue() * 100, 2)
                : 0,
            'outstanding_invoices' => $outstandingKPI?->getCurrentValue() ?? 0,
            'collection_rate' => $collectionRateKPI?->getCurrentValue() ?? 0,
        ];
    }

    private function filterKPIsByCategory(array $kpis, string $category): array
    {
        return array_filter($kpis, fn($kpi) => $kpi->type->getCategory() === $category);
    }

    private function formatKPIArray(array $kpis): array
    {
        return array_map(fn($kpi) => [
            'name' => $kpi->name,
            'type' => $kpi->type->value,
            'current_value' => $kpi->getCurrentValue(),
            'target' => $kpi->getCurrentTarget(),
            'status' => $kpi->getStatus(),
            'trend' => $kpi->getTrendDirection()->value,
        ], $kpis);
    }
}
