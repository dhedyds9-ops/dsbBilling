<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\RackUnit;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RackUnitRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?RackUnit;
    public function findByRack(Uuid $rackId): array;
    public function findAvailableInRack(Uuid $rackId): array;
    public function findByPosition(Uuid $rackId, int $position): ?RackUnit;
    public function save(RackUnit $unit): void;
    public function delete(RackUnit $unit): void;
}
