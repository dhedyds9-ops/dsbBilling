<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetWarranty;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetWarrantyRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetWarranty;
    public function findByAsset(Uuid $assetId): array;
    public function findActiveByAsset(Uuid $assetId): ?AssetWarranty;
    public function findExpiringWithin(int $days): array;
    public function findByVendor(Uuid $vendorId): array;
    public function save(AssetWarranty $warranty): void;
    public function delete(AssetWarranty $warranty): void;
}
