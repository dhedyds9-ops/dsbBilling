<?php

namespace Src\Domain\Analytics\Repositories;

use Src\Domain\Analytics\CoverageAnalytics;
use Src\Domain\Analytics\Enums\AnalyticsType;
use Src\Domain\Analytics\Enums\TimeRange;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CoverageAnalyticsRepositoryInterface
{
    public function save(CoverageAnalytics $analytics): void;
    
    public function findById(Uuid $id): ?CoverageAnalytics;
    
    public function findByAreaId(string $areaId): array;
    
    public function findByType(AnalyticsType $type): array;
    
    public function findByTimeRange(TimeRange $timeRange): array;
    
    public function findLatestByArea(string $areaId): ?CoverageAnalytics;
    
    public function findHistorical(string $areaId, int $limit = 12): array;
    
    public function delete(Uuid $id): void;
}
