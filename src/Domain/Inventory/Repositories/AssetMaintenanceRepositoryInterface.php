<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetMaintenance;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetMaintenanceRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetMaintenance;
    public function findByAsset(Uuid $assetId): array;
    public function findScheduledByAsset(Uuid $assetId): array;
    public function findUpcoming(int $days): array;
    public function findOverdue(): array;
    public function findByTechnician(Uuid $technicianId): array;
    public function findByStatus(string $status): array;
    public function save(AssetMaintenance $maintenance): void;
    public function delete(AssetMaintenance $maintenance): void;
}
