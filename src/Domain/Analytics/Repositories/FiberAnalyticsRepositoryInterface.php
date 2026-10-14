<?php

namespace Src\Domain\Analytics\Repositories;

use Src\Domain\Analytics\FiberAnalytics;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface FiberAnalyticsRepositoryInterface
{
    public function save(FiberAnalytics $analytics): void;
    
    public function findById(Uuid $id): ?FiberAnalytics;
    
    public function findByAreaId(string $areaId): array;
    
    public function findLatestByArea(string $areaId): ?FiberAnalytics;
    
    public function findCriticalUtilization(float $threshold = 80): array;
    
    public function findByUtilizationRange(float $min, float $max): array;
    
    public function delete(Uuid $id): void;
}
