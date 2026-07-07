<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\VendorPurchase;
use Src\Domain\Inventory\GoodsReceipt;
use Src\Domain\Inventory\Repositories\VendorPurchaseRepositoryInterface;
use Src\Domain\Inventory\Repositories\GoodsReceiptRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class ProcurementService
{
    public function __construct(
        private readonly VendorPurchaseRepositoryInterface $purchaseRepository,
        private readonly GoodsReceiptRepositoryInterface $receiptRepository
    ) {}

    public function createPurchaseOrder(
        Uuid $vendorId,
        string $purchaseNumber,
        float $totalAmount,
        ?DateTimeImmutable $expectedDelivery = null,
        ?string $notes = null
    ): VendorPurchase {
        $purchase = VendorPurchase::create(
            vendorId: $vendorId,
            purchaseNumber: $purchaseNumber,
            purchaseDate: new DateTimeImmutable(),
            totalAmount: $totalAmount,
            notes: $notes,
            expectedDelivery: $expectedDelivery
        );

        $this->purchaseRepository->save($purchase);

        return $purchase;
    }

    public function approvePurchase(Uuid $purchaseId, Uuid $approvedBy): VendorPurchase
    {
        $purchase = $this->purchaseRepository->findById($purchaseId);
        if (!$purchase) {
            throw new \DomainException("Purchase order not found");
        }

        $purchase->approve();
        $this->purchaseRepository->save($purchase);

        return $purchase;
    }

    public function receiveGoods(
        Uuid $purchaseId,
        string $receiptNumber,
        Uuid $warehouseId,
        Uuid $receivedBy,
        array $items
    ): GoodsReceipt {
        $purchase = $this->purchaseRepository->findById($purchaseId);
        if (!$purchase) {
            throw new \DomainException("Purchase order not found");
        }

        $receipt = GoodsReceipt::create(
            receiptNumber: $receiptNumber,
            vendorId: $purchase->vendorId,
            warehouseId: $warehouseId,
            receivedBy: $receivedBy,
            purchaseOrderId: $purchaseId
        );

        foreach ($items as $item) {
            $receipt->addItem(
                productId: new Uuid($item['product_id']),
                quantity: $item['quantity'],
                serialNumber: $item['serial_number'] ?? null
            );
        }

        $this->receiptRepository->save($receipt);

        $purchase->receive($receipt->id);
        $this->purchaseRepository->save($purchase);

        return $receipt;
    }

    public function getPendingPurchaseOrders(): array
    {
        return $this->purchaseRepository->findPending();
    }

    public function getPurchaseOrdersByVendor(Uuid $vendorId): array
    {
        return $this->purchaseRepository->findByVendor($vendorId);
    }

    public function getReceiptsByPurchase(Uuid $purchaseId): array
    {
        return $this->receiptRepository->findByPurchaseOrder($purchaseId);
    }

    public function calculateTotalReceived(Uuid $purchaseId): float
    {
        $receipts = $this->receiptRepository->findByPurchaseOrder($purchaseId);
        return array_sum(array_map(fn($r) => $r->getTotalReceivedQuantity(), $receipts));
    }
}
