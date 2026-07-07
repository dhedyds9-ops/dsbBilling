<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\VendorPurchase;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface VendorPurchaseRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?VendorPurchase;
    public function findByNumber(string $number): ?VendorPurchase;
    public function findByVendor(Uuid $vendorId): array;
    public function findPending(): array;
    public function findByStatus(string $status): array;
    public function save(VendorPurchase $purchase): void;
    public function delete(VendorPurchase $purchase): void;
}
