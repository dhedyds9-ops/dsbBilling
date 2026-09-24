<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\OLTCapacity;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface OLTCapacityRepositoryInterface
{
    public function save(OLTCapacity $capacity): void;
    
    public function findById(Uuid $id): ?OLTCapacity;
    
    public function findByOltId(Uuid $oltId): ?OLTCapacity;
    
    public function findByStatus(CapacityStatus $status): array;
    
    public function findWithAvailableCapacity(): array;
    
    public function delete(Uuid $id): void;
}
