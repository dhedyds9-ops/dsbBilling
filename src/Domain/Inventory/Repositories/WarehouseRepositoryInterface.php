<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\Warehouse;
use Src\Domain\Inventory\WarehouseLocation;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface WarehouseRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?Warehouse;
    public function findByCode(string $code): ?Warehouse;
    public function findByType(string $type): array;
    public function findActive(): array;
    public function save(Warehouse $warehouse): void;
    public function delete(Warehouse $warehouse): void;
}

interface WarehouseLocationRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?WarehouseLocation;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByParent(Uuid $parentId): array;
    public function findRootLocations(Uuid $warehouseId): array;
    public function save(WarehouseLocation $location): void;
    public function delete(WarehouseLocation $location): void;
}
