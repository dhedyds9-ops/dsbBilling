<?php

namespace Src\Domain\BusinessIntelligence\Enums;

enum DashboardStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case SHARED = 'shared';

    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
            self::SHARED => 'Shared',
        };
    }
}

enum WidgetType: string
{
    case LINE_CHART = 'line_chart';
    case BAR_CHART = 'bar_chart';
    case PIE_CHART = 'pie_chart';
    case DOUGHNUT_CHART = 'doughnut_chart';
    case AREA_CHART = 'area_chart';
    case GAUGE = 'gauge';
    case NUMBER = 'number';
    case TABLE = 'table';
    case HEATMAP = 'heatmap';
    case KPI_CARD = 'kpi_card';
    case TREND_INDICATOR = 'trend_indicator';
    case FUNNEL = 'funnel';
    case SCATTER_PLOT = 'scatter_plot';

    public function getLabel(): string
    {
        return match($this) {
            self::LINE_CHART => 'Line Chart',
            self::BAR_CHART => 'Bar Chart',
            self::PIE_CHART => 'Pie Chart',
            self::DOUGHNUT_CHART => 'Doughnut Chart',
            self::AREA_CHART => 'Area Chart',
            self::GAUGE => 'Gauge',
            self::NUMBER => 'Number',
            self::TABLE => 'Table',
            self::HEATMAP => 'Heatmap',
            self::KPI_CARD => 'KPI Card',
            self::TREND_INDICATOR => 'Trend Indicator',
            self::FUNNEL => 'Funnel',
            self::SCATTER_PLOT => 'Scatter Plot',
        };
    }
}

enum ReportStatus: string
{
    case DRAFT = 'draft';
    case GENERATING = 'generating';
    case READY = 'ready';
    case FAILED = 'failed';
    case SCHEDULED = 'scheduled';

    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::GENERATING => 'Generating',
            self::READY => 'Ready',
            self::FAILED => 'Failed',
            self::SCHEDULED => 'Scheduled',
        };
    }
}

enum DataGranularity: string
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match($this) {
            self::HOURLY => 'Hourly',
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::YEARLY => 'Yearly',
        };
    }

    public function toDateInterval(): string
    {
        return match($this) {
            self::HOURLY => 'PT1H',
            self::DAILY => 'P1D',
            self::WEEKLY => 'P1W',
            self::MONTHLY => 'P1M',
            self::QUARTERLY => 'P3M',
            self::YEARLY => 'P1Y',
        };
    }
}

enum ForecastModel: string
{
    case LINEAR_REGRESSION = 'linear_regression';
    case EXPONENTIAL_SMOOTHING = 'exponential_smoothing';
    case ARIMA = 'arima';
    case MOVING_AVERAGE = 'moving_average';
    case POLYNOMIAL = 'polynomial';
    case PROPHET = 'prophet';

    public function getLabel(): string
    {
        return match($this) {
            self::LINEAR_REGRESSION => 'Linear Regression',
            self::EXPONENTIAL_SMOOTHING => 'Exponential Smoothing',
            self::ARIMA => 'ARIMA',
            self::MOVING_AVERAGE => 'Moving Average',
            self::POLYNOMIAL => 'Polynomial',
            self::PROPHET => 'Prophet',
        };
    }

    public function isTimeSeries(): bool
    {
        return match($this) {
            self::EXPONENTIAL_SMOOTHING, self::ARIMA, self::PROPHET, self::MOVING_AVERAGE => true,
            default => false,
        };
    }
}

enum TrendDirection: string
{
    case UP = 'up';
    case DOWN = 'down';
    case STABLE = 'stable';

    public function getLabel(): string
    {
        return match($this) {
            self::UP => 'Trending Up',
            self::DOWN => 'Trending Down',
            self::STABLE => 'Stable',
        };
    }
}

enum ComparisonPeriod: string
{
    case PREVIOUS_PERIOD = 'previous_period';
    case SAME_PERIOD_LAST_YEAR = 'same_period_last_year';
    case LAST_30_DAYS = 'last_30_days';
    case LAST_90_DAYS = 'last_90_days';
    case YEAR_TO_DATE = 'year_to_date';

    public function getLabel(): string
    {
        return match($this) {
            self::PREVIOUS_PERIOD => 'Previous Period',
            self::SAME_PERIOD_LAST_YEAR => 'Same Period Last Year',
            self::LAST_30_DAYS => 'Last 30 Days',
            self::LAST_90_DAYS => 'Last 90 Days',
            self::YEAR_TO_DATE => 'Year to Date',
        };
    }
}
