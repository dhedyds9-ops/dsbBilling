<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\FiberCapacity;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface FiberCapacityRepositoryInterface
{
    public function save(FiberCapacity $capacity): void;
    
    public function findById(Uuid $id): ?FiberCapacity;
    
    public function findByResourceId(Uuid $resourceId, string $resourceType): ?FiberCapacity;
    
    public function findByResourceType(string $resourceType): array;
    
    public function findByStatus(CapacityStatus $status): array;
    
    public function findByUtilizationRange(float $minPercentage, float $maxPercentage): array;
    
    public function delete(Uuid $id): void;
}
