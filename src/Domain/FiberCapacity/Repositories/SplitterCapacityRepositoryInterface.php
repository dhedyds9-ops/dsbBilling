<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\SplitterCapacity;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface SplitterCapacityRepositoryInterface
{
    public function save(SplitterCapacity $capacity): void;
    
    public function findById(Uuid $id): ?SplitterCapacity;
    
    public function findBySplitterId(Uuid $splitterId): ?SplitterCapacity;
    
    public function findByOdpId(Uuid $odpId): array;
    
    public function findByStatus(CapacityStatus $status): array;
    
    public function findAvailableSplitters(Uuid $odpId): array;
    
    public function delete(Uuid $id): void;
}
