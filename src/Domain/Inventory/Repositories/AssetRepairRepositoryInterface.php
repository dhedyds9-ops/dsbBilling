<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetRepair;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetRepairRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetRepair;
    public function findByAsset(Uuid $assetId): array;
    public function findByTechnician(Uuid $technicianId): array;
    public function findPending(): array;
    public function findInProgress(): array;
    public function findCompleted(): array;
    public function save(AssetRepair $repair): void;
    public function delete(AssetRepair $repair): void;
}
