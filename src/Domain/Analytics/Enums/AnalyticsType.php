<?php

namespace Src\Domain\Analytics\Enums;

enum AnalyticsType: string
{
    case COVERAGE = 'coverage';
    case FIBER_UTILIZATION = 'fiber_utilization';
    case ODP_UTILIZATION = 'odp_utilization';
    case OLT_UTILIZATION = 'olt_utilization';
    case CUSTOMER_DENSITY = 'customer_density';
    case HEATMAP = 'heatmap';
    case GROWTH_TREND = 'growth_trend';
    case EXPANSION = 'expansion';

    public function label(): string
    {
        return match($this) {
            self::COVERAGE => 'Coverage Analysis',
            self::FIBER_UTILIZATION => 'Fiber Utilization',
            self::ODP_UTILIZATION => 'ODP Utilization',
            self::OLT_UTILIZATION => 'OLT Utilization',
            self::CUSTOMER_DENSITY => 'Customer Density',
            self::HEATMAP => 'Network Heatmap',
            self::GROWTH_TREND => 'Growth Trend',
            self::EXPANSION => 'Expansion Recommendation',
        };
    }

    public function refreshInterval(): int
    {
        return match($this) {
            self::COVERAGE => 3600,
            self::FIBER_UTILIZATION => 1800,
            self::ODP_UTILIZATION => 1800,
            self::OLT_UTILIZATION => 1800,
            self::CUSTOMER_DENSITY => 86400,
            self::HEATMAP => 900,
            self::GROWTH_TREND => 86400,
            self::EXPANSION => 604800,
        };
    }
}
