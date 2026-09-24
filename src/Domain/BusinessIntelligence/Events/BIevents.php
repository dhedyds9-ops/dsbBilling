<?php

namespace Src\Domain\BusinessIntelligence\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class DashboardCreated extends DomainEvent
{
    public function __construct(
        public readonly Uuid $dashboardId,
        public readonly string $name,
        public readonly string $module,
        public readonly ?Uuid $createdBy = null
    ) {
        parent::__construct(
            aggregateId: $dashboardId,
            eventType: 'dashboard.created',
            occurredOn: new \DateTimeImmutable()
        );
    }

    public function toArray(): array
    {
        return [
            'dashboard_id' => $this->dashboardId->toString(),
            'name' => $this->name,
            'module' => $this->module,
            'created_by' => $this->createdBy?->toString(),
            'occurred_at' => $this->occurredOn->format('Y-m-d H:i:s'),
        ];
    }
}

class DashboardPublished extends DomainEvent
{
    public function __construct(
        public readonly Uuid $dashboardId,
        public readonly string $name
    ) {
        parent::__construct(
            aggregateId: $dashboardId,
            eventType: 'dashboard.published',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class DashboardShared extends DomainEvent
{
    public function __construct(
        public readonly Uuid $dashboardId,
        public readonly array $sharedWith
    ) {
        parent::__construct(
            aggregateId: $dashboardId,
            eventType: 'dashboard.shared',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class WidgetAdded extends DomainEvent
{
    public function __construct(
        public readonly Uuid $dashboardId,
        public readonly Uuid $widgetId,
        public readonly string $widgetName
    ) {
        parent::__construct(
            aggregateId: $dashboardId,
            eventType: 'widget.added',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class KPIRecorded extends DomainEvent
{
    public function __construct(
        public readonly Uuid $kpiId,
        public readonly string $kpiType,
        public readonly float $value,
        public readonly ?float $previousValue = null,
        public readonly ?float $targetValue = null
    ) {
        parent::__construct(
            aggregateId: $kpiId,
            eventType: 'kpi.recorded',
            occurredOn: new \DateTimeImmutable()
        );
    }

    public function getChangePercentage(): ?float
    {
        if ($this->previousValue === null || $this->previousValue == 0) {
            return null;
        }
        return (($this->value - $this->previousValue) / $this->previousValue) * 100;
    }
}

class KPIThresholdBreached extends DomainEvent
{
    public function __construct(
        public readonly Uuid $kpiId,
        public readonly string $kpiType,
        public readonly float $value,
        public readonly string $thresholdType, // warning, critical
        public readonly float $thresholdValue
    ) {
        parent::__construct(
            aggregateId: $kpiId,
            eventType: 'kpi.threshold_breached',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class ReportGenerated extends DomainEvent
{
    public function __construct(
        public readonly Uuid $reportId,
        public readonly string $reportName,
        public readonly string $filePath,
        public readonly int $executionTimeMs
    ) {
        parent::__construct(
            aggregateId: $reportId,
            eventType: 'report.generated',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class ReportScheduled extends DomainEvent
{
    public function __construct(
        public readonly Uuid $reportId,
        public readonly string $cronExpression,
        public readonly array $recipients
    ) {
        parent::__construct(
            aggregateId: $reportId,
            eventType: 'report.scheduled',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class ForecastGenerated extends DomainEvent
{
    public function __construct(
        public readonly Uuid $forecastId,
        public readonly string $metricName,
        public readonly string $model,
        public readonly float $accuracy,
        public readonly array $predictions
    ) {
        parent::__construct(
            aggregateId: $forecastId,
            eventType: 'forecast.generated',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class DataCubeRefreshed extends DomainEvent
{
    public function __construct(
        public readonly Uuid $cubeId,
        public readonly string $cubeName,
        public readonly int $rowCount,
        public readonly int $refreshDurationMs
    ) {
        parent::__construct(
            aggregateId: $cubeId,
            eventType: 'datacube.refreshed',
            occurredOn: new \DateTimeImmutable()
        );
    }
}

class AnalyticsComputed extends DomainEvent
{
    public function __construct(
        public readonly Uuid $analyticsId,
        public readonly string $analyticsName,
        public readonly string $type,
        public readonly array $metrics
    ) {
        parent::__construct(
            aggregateId: $analyticsId,
            eventType: 'analytics.computed',
            occurredOn: new \DateTimeImmutable()
        );
    }
}
