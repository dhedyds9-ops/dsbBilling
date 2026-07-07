<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\StockAdjustment;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface StockAdjustmentRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?StockAdjustment;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByReason(string $reason): array;
    public function findPending(): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(StockAdjustment $adjustment): void;
    public function delete(StockAdjustment $adjustment): void;
}
