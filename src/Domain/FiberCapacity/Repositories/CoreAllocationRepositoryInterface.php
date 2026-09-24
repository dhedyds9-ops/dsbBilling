<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\CoreAllocation;
use Src\Domain\FiberCapacity\Enums\AllocationStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CoreAllocationRepositoryInterface
{
    public function save(CoreAllocation $allocation): void;
    
    public function findById(Uuid $id): ?CoreAllocation;
    
    public function findByFiberCoreId(Uuid $fiberCoreId): array;
    
    public function findActiveByFiberCableId(Uuid $fiberCableId): array;
    
    public function findByAllocatedTo(string $allocatedTo): array;
    
    public function findByStatus(AllocationStatus $status): array;
    
    public function findExpiredAllocations(): array;
    
    public function delete(Uuid $id): void;
}
