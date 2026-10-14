<?php

namespace Src\Domain\Analytics\Repositories;

use Src\Domain\Analytics\CustomerDistribution;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CustomerDistributionRepositoryInterface
{
    public function save(CustomerDistribution $distribution): void;
    
    public function findById(Uuid $id): ?CustomerDistribution;
    
    public function findByAreaId(string $areaId): array;
    
    public function findLatestByArea(string $areaId): ?CustomerDistribution;
    
    public function findHotspots(): array;
    
    public function findColdspots(): array;
    
    public function delete(Uuid $id): void;
}
