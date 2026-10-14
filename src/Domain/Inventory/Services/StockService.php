<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\StockMovement;
use Src\Domain\Inventory\StockAdjustment;
use Src\Domain\Inventory\StockOpname;
use Src\Domain\Inventory\GoodsIssue;
use Src\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use Src\Domain\Inventory\Repositories\StockAdjustmentRepositoryInterface;
use Src\Domain\Inventory\Repositories\StockOpnameRepositoryInterface;
use Src\Domain\Inventory\Repositories\GoodsIssueRepositoryInterface;
use Src\Domain\Inventory\Repositories\InventoryItemRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class StockService
{
    public function __construct(
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly StockAdjustmentRepositoryInterface $adjustmentRepository,
        private readonly StockOpnameRepositoryInterface $opnameRepository,
        private readonly GoodsIssueRepositoryInterface $issueRepository,
        private readonly InventoryItemRepositoryInterface $inventoryRepository
    ) {}

    public function receiveStock(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $warehouseId,
        Uuid $receivedBy,
        ?Uuid $referenceId = null,
        ?string $referenceType = null
    ): StockMovement {
        $item = $this->inventoryRepository->findById($inventoryItemId);
        if (!$item) {
            throw new \DomainException("Inventory item not found");
        }

        $item->receive($quantity);
        $this->inventoryRepository->save($item);

        $movement = StockMovement::receive(
            inventoryItemId: $inventoryItemId,
            quantity: $quantity,
            toWarehouseId: $warehouseId,
            performedBy: $receivedBy,
            referenceId: $referenceId,
            referenceType: $referenceType
        );

        $this->movementRepository->save($movement);

        return $movement;
    }

    public function issueStock(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $warehouseId,
        Uuid $issuedTo,
        string $issuedToType,
        Uuid $issuedBy,
        ?string $purpose = null
    ): GoodsIssue {
        $item = $this->inventoryRepository->findById($inventoryItemId);
        if (!$item) {
            throw new \DomainException("Inventory item not found");
        }

        if ($quantity > $item->getAvailableQuantity()) {
            throw new \DomainException("Insufficient stock");
        }

        $issueNumber = 'GI-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $issue = GoodsIssue::create(
            issueNumber: $issueNumber,
            warehouseId: $warehouseId,
            issuedBy: $issuedBy,
            issuedTo: $issuedTo,
            issuedToType: $issuedToType,
            purpose: $purpose
        );

        $issue->addItem($inventoryItemId, $quantity);
        $issue->approve();
        $issue->issue();

        $this->issueRepository->save($issue);

        $item->issue($quantity);
        $this->inventoryRepository->save($item);

        return $issue;
    }

    public function adjustStock(
        Uuid $inventoryItemId,
        int $newQuantity,
        string $reason,
        Uuid $adjustedBy,
        ?string $notes = null
    ): StockAdjustment {
        $item = $this->inventoryRepository->findById($inventoryItemId);
        if (!$item) {
            throw new \DomainException("Inventory item not found");
        }

        $adjustmentNumber = 'SA-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $adjustment = StockAdjustment::create(
            adjustmentNumber: $adjustmentNumber,
            warehouseId: $item->warehouseId,
            adjustedBy: $adjustedBy,
            reason: $reason,
            notes: $notes
        );

        $adjustment->addItem(
            inventoryItemId: $inventoryItemId,
            currentQuantity: $item->quantity,
            newQuantity: $newQuantity
        );

        $adjustment->submit();
        $adjustment->approve($adjustedBy);
        $adjustment->complete();

        $this->adjustmentRepository->save($adjustment);

        $item->adjust($newQuantity - $item->quantity, $reason);
        $this->inventoryRepository->save($item);

        return $adjustment;
    }

    public function transferStock(
        Uuid $inventoryItemId,
        int $quantity,
        Uuid $fromWarehouseId,
        Uuid $toWarehouseId,
        Uuid $initiatedBy
    ): array {
        $item = $this->inventoryRepository->findById($inventoryItemId);
        if (!$item) {
            throw new \DomainException("Inventory item not found");
        }

        if ($quantity > $item->getAvailableQuantity()) {
            throw new \DomainException("Insufficient stock for transfer");
        }

        $movements = StockMovement::transfer(
            inventoryItemId: $inventoryItemId,
            quantity: $quantity,
            fromWarehouseId: $fromWarehouseId,
            toWarehouseId: $toWarehouseId,
            performedBy: $initiatedBy
        );

        foreach ($movements as $movement) {
            $this->movementRepository->save($movement);
        }

        $item->issue($quantity);
        $item->warehouseId = $toWarehouseId;
        $item->receive($quantity);
        $this->inventoryRepository->save($item);

        return $movements;
    }

    public function conductStockOpname(
        Uuid $warehouseId,
        string $opnameNumber,
        Uuid $conductedBy,
        DateTimeImmutable $scheduledAt
    ): StockOpname {
        $opname = StockOpname::schedule(
            opnameNumber: $opnameNumber,
            warehouseId: $warehouseId,
            conductedBy: $conductedBy,
            scheduledAt: $scheduledAt
        );

        $this->opnameRepository->save($opname);

        return $opname;
    }

    public function recordOpnameCount(
        Uuid $opnameId,
        Uuid $inventoryItemId,
        int $systemQuantity,
        int $countedQuantity,
        ?string $notes = null
    ): void {
        $opname = $this->opnameRepository->findById($opnameId);
        if (!$opname) {
            throw new \DomainException("Stock opname not found");
        }

        $opname->recordCount(
            inventoryItemId: $inventoryItemId,
            systemQuantity: $systemQuantity,
            countedQuantity: $countedQuantity,
            notes: $notes
        );

        $this->opnameRepository->save($opname);
    }

    public function completeOpname(Uuid $opnameId): StockOpname
    {
        $opname = $this->opnameRepository->findById($opnameId);
        if (!$opname) {
            throw new \DomainException("Stock opname not found");
        }

        $opname->start();
        $opname->requestDiscrepancyReview();

        if ($opname->status === StockOpname::STATUS_COMPLETED) {
            // No discrepancies found
        } else {
            // Has discrepancies - needs review
        }

        $this->opnameRepository->save($opname);

        return $opname;
    }

    public function getStockMovements(Uuid $inventoryItemId, ?int $limit = 100): array
    {
        return $this->movementRepository->findByItem($inventoryItemId, $limit);
    }

    public function getLowStockItems(Uuid $warehouseId): array
    {
        return $this->inventoryRepository->findLowStock($warehouseId);
    }

    public function getStockValuation(Uuid $warehouseId): array
    {
        $items = $this->inventoryRepository->findByWarehouse($warehouseId);
        
        $totalValue = 0;
        foreach ($items as $item) {
            $totalValue += $item->quantity * ($item->metadata['unit_cost'] ?? 0);
        }

        return [
            'warehouse_id' => $warehouseId->value,
            'total_items' => count($items),
            'total_value' => $totalValue,
            'items' => $items
        ];
    }
}
