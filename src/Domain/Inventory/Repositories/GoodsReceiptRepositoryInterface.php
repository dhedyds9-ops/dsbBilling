<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\GoodsReceipt;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GoodsReceiptRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?GoodsReceipt;
    public function findByNumber(string $number): ?GoodsReceipt;
    public function findByPurchaseOrder(Uuid $purchaseOrderId): array;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(GoodsReceipt $receipt): void;
    public function delete(GoodsReceipt $receipt): void;
}
