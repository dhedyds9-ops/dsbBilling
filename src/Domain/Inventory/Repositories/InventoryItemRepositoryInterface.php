<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\InventoryItem;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface InventoryItemRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?InventoryItem;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByProduct(Uuid $productId): array;
    public function findLowStock(Uuid $warehouseId): array;
    public function findBySku(string $sku): ?InventoryItem;
    public function search(string $query): array;
    public function save(InventoryItem $item): void;
    public function delete(InventoryItem $item): void;
}
