<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\Asset;
use Src\Domain\Inventory\Enums\AssetStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?Asset;
    public function findByCode(string $code): ?Asset;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByStatus(AssetStatus $status): array;
    public function findByCategory(Uuid $categoryId): array;
    public function findByAssignment(Uuid $entityId): array;
    public function findExpiringWarranty(int $days): array;
    public function findRequiringMaintenance(): array;
    public function search(string $query): array;
    public function save(Asset $asset): void;
    public function delete(Asset $asset): void;
}
