<?php

namespace Src\Domain\BusinessIntelligence\Services;

use Src\Domain\BusinessIntelligence\Dashboard;
use Src\Domain\BusinessIntelligence\Widget;
use Src\Domain\BusinessIntelligence\Repositories\DashboardRepositoryInterface;
use Src\Domain\BusinessIntelligence\Repositories\WidgetRepositoryInterface;
use Src\Domain\BusinessIntelligence\Events\DashboardCreated;
use Src\Domain\BusinessIntelligence\Events\DashboardPublished;
use Src\Domain\BusinessIntelligence\Events\WidgetAdded;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
        private readonly WidgetRepositoryInterface $widgetRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function createDashboard(
        string $name,
        string $description,
        string $module,
        ?Uuid $createdBy = null
    ): Dashboard {
        $dashboard = Dashboard::create(
            name: $name,
            description: $description,
            module: $module,
            createdBy: $createdBy
        );

        $this->dashboardRepository->save($dashboard);

        $this->eventDispatcher->dispatch(new DashboardCreated(
            dashboardId: $dashboard->id,
            name: $dashboard->name,
            module: $dashboard->module,
            createdBy: $createdBy
        ));

        return $dashboard;
    }

    public function addWidget(
        Uuid $dashboardId,
        string $widgetName,
        string $widgetType,
        int $positionX,
        int $positionY,
        int $width,
        int $height,
        ?Uuid $createdBy = null
    ): Widget {
        $dashboard = $this->dashboardRepository->findById($dashboardId);

        if (!$dashboard) {
            throw new \DomainException('Dashboard not found');
        }

        $widget = Widget::create(
            name: $widgetName,
            type: \Src\Domain\BusinessIntelligence\Enums\WidgetType::from($widgetType),
            dashboardId: $dashboardId,
            createdBy: $createdBy
        );

        $this->widgetRepository->save($widget);

        $dashboard->addWidget(
            widgetId: $widget->id,
            positionX: $positionX,
            positionY: $positionY,
            width: $width,
            height: $height
        );

        $this->dashboardRepository->save($dashboard);

        $this->eventDispatcher->dispatch(new WidgetAdded(
            dashboardId: $dashboardId,
            widgetId: $widget->id,
            widgetName: $widgetName
        ));

        return $widget;
    }

    public function publishDashboard(Uuid $dashboardId): Dashboard
    {
        $dashboard = $this->dashboardRepository->findById($dashboardId);

        if (!$dashboard) {
            throw new \DomainException('Dashboard not found');
        }

        $dashboard->publish();
        $this->dashboardRepository->save($dashboard);

        $this->eventDispatcher->dispatch(new DashboardPublished(
            dashboardId: $dashboard->id,
            name: $dashboard->name
        ));

        return $dashboard;
    }

    public function shareDashboard(Uuid $dashboardId, array $recipients): Dashboard
    {
        $dashboard = $this->dashboardRepository->findById($dashboardId);

        if (!$dashboard) {
            throw new \DomainException('Dashboard not found');
        }

        $dashboard->share();
        $this->dashboardRepository->save($dashboard);

        // Dispatch share event (would include recipients in real implementation)
        return $dashboard;
    }

    public function getDashboardWithWidgets(Uuid $dashboardId): ?Dashboard
    {
        return $this->dashboardRepository->findById($dashboardId);
    }

    public function getDashboardsByModule(string $module): array
    {
        return $this->dashboardRepository->findByModule($module);
    }

    public function getSharedDashboards(): array
    {
        return $this->dashboardRepository->findShared();
    }

    public function archiveDashboard(Uuid $dashboardId): void
    {
        $dashboard = $this->dashboardRepository->findById($dashboardId);

        if (!$dashboard) {
            throw new \DomainException('Dashboard not found');
        }

        $dashboard->archive();
        $this->dashboardRepository->save($dashboard);
    }
}
