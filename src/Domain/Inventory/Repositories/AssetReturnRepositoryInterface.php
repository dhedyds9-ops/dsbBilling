<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetReturn;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetReturnRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetReturn;
    public function findByAsset(Uuid $assetId): array;
    public function findByReturnedBy(Uuid $userId): array;
    public function findPendingInspection(): array;
    public function findRequiresRepair(): array;
    public function save(AssetReturn $return): void;
    public function delete(AssetReturn $return): void;
}
