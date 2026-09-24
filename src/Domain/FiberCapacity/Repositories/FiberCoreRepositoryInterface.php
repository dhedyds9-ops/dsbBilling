<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\FiberCore;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface FiberCoreRepositoryInterface
{
    public function save(FiberCore $fiberCore): void;
    
    public function findById(Uuid $id): ?FiberCore;
    
    public function findByFiberCableId(Uuid $fiberCableId): array;
    
    public function findAvailableCores(Uuid $fiberCableId, int $required = 1): array;
    
    public function delete(Uuid $id): void;
}
