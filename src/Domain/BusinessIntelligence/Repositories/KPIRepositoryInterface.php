<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\KPI;
use Src\Domain\BusinessIntelligence\Enums\KPIType;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface KPIRepositoryInterface
{
    public function findById(Uuid $id): ?KPI;

    public function findByType(KPIType $type): ?KPI;

    public function save(KPI $kpi): void;

    public function delete(Uuid $id): void;

    public function findByModule(string $module): array;

    public function findByGranularity(DataGranularity $granularity): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findActiveKPIs(): array;

    public function findKPIsNeedingRecalculation(): array;
}
