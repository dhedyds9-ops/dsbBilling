<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\Dashboard;
use Src\Domain\BusinessIntelligence\Enums\DashboardStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface DashboardRepositoryInterface
{
    public function findById(Uuid $id): ?Dashboard;

    public function findByName(string $name): ?Dashboard;

    public function save(Dashboard $dashboard): void;

    public function delete(Uuid $id): void;

    public function findByStatus(DashboardStatus $status): array;

    public function findByModule(string $module): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findShared(): array;
}
