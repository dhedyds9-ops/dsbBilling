<?php

namespace Src\Domain\FiberCapacity\Repositories;

use Src\Domain\FiberCapacity\PonCapacity;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Enums\PortStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface PonCapacityRepositoryInterface
{
    public function save(PonCapacity $capacity): void;
    
    public function findById(Uuid $id): ?PonCapacity;
    
    public function findByPonPortId(Uuid $ponPortId): ?PonCapacity;
    
    public function findByOltId(Uuid $oltId): array;
    
    public function findByStatus(CapacityStatus $status): array;
    
    public function findByPortStatus(PortStatus $status): array;
    
    public function findAvailablePonPorts(Uuid $oltId): array;
    
    public function delete(Uuid $id): void;
}
