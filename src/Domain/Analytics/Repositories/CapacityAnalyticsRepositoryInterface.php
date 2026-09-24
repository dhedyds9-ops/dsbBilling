<?php

namespace Src\Domain\Analytics\Repositories;

use Src\Domain\Analytics\CapacityAnalytics;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CapacityAnalyticsRepositoryInterface
{
    public function save(CapacityAnalytics $analytics): void;
    
    public function findById(Uuid $id): ?CapacityAnalytics;
    
    public function findByAreaId(string $areaId): array;
    
    public function findLatestByArea(string $areaId): ?CapacityAnalytics;
    
    public function findCriticalResources(): array;
    
    public function findWarningResources(): array;
    
    public function delete(Uuid $id): void;
}
