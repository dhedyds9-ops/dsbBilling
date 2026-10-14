<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\StockMovement;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface StockMovementRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?StockMovement;
    public function findByItem(Uuid $inventoryItemId, ?int $limit = null): array;
    public function findByWarehouse(Uuid $warehouseId, ?int $limit = null): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function findByReference(string $referenceType, Uuid $referenceId): array;
    public function save(StockMovement $movement): void;
    public function delete(StockMovement $movement): void;
}
