<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\StockOpname;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface StockOpnameRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?StockOpname;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findPending(): array;
    public function findInProgress(): array;
    public function findCompleted(): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(StockOpname $opname): void;
    public function delete(StockOpname $opname): void;
}
