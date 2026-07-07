<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetTransfer;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetTransferRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetTransfer;
    public function findByAsset(Uuid $assetId): array;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findPending(): array;
    public function findInTransit(): array;
    public function findByStatus(string $status): array;
    public function save(AssetTransfer $transfer): void;
    public function delete(AssetTransfer $transfer): void;
}
