<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetDepreciation;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetDepreciationRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetDepreciation;
    public function findByAsset(Uuid $assetId): array;
    public function findLatestByAsset(Uuid $assetId): ?AssetDepreciation;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(AssetDepreciation $depreciation): void;
    public function delete(AssetDepreciation $depreciation): void;
}
