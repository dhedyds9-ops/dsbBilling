<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\Widget;
use Src\Domain\BusinessIntelligence\Enums\WidgetType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WidgetRepositoryInterface
{
    public function findById(Uuid $id): ?Widget;

    public function save(Widget $widget): void;

    public function delete(Uuid $id): void;

    public function findByDashboard(Uuid $dashboardId): array;

    public function findByType(WidgetType $type): array;

    public function findByCreator(Uuid $creatorId): array;
}
