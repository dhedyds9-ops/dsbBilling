<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\Asset;
use Src\Domain\Inventory\AssetCategory;
use Src\Domain\Inventory\Enums\AssetStatus;
use Src\Domain\Inventory\InventoryItem;
use Src\Domain\Inventory\StockMovement;
use Src\Domain\Inventory\Repositories\AssetRepositoryInterface;
use Src\Domain\Inventory\Repositories\InventoryItemRepositoryInterface;
use Src\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class InventoryService
{
    public function __construct(
        private readonly AssetRepositoryInterface $assetRepository,
        private readonly InventoryItemRepositoryInterface $inventoryRepository,
        private readonly StockMovementRepositoryInterface $stockMovementRepository
    ) {}

    public function receiveAsset(
        Uuid $assetId,
        Uuid $warehouseId,
        Uuid $receivedBy,
        ?Uuid $purchaseOrderId = null
    ): StockMovement {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $movement = StockMovement::receive(
            inventoryItemId: $assetId,
            quantity: 1,
            toWarehouseId: $warehouseId,
            performedBy: $receivedBy,
            referenceId: $purchaseOrderId,
            referenceType: 'purchase_order'
        );

        $this->stockMovementRepository->save($movement);

        $asset->markAsInstalled();
        $this->assetRepository->save($asset);

        return $movement;
    }

    public function issueAsset(
        Uuid $assetId,
        Uuid $toEntityId,
        string $toEntityType,
        Uuid $issuedBy,
        ?string $notes = null
    ): Asset {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        if (!$asset->getStatus()->canBeAssigned()) {
            throw new \DomainException("Asset cannot be assigned in current status");
        }

        $asset->assignTo($toEntityId, new Uuid($toEntityType));

        $this->assetRepository->save($asset);

        return $asset;
    }

    public function transferAsset(
        Uuid $assetId,
        Uuid $toWarehouseId,
        Uuid $initiatedBy,
        ?string $reason = null
    ): array {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        if (!$asset->getStatus()->canBeTransferred()) {
            throw new \DomainException("Asset cannot be transferred in current status");
        }

        $movements = StockMovement::transfer(
            inventoryItemId: $assetId,
            quantity: 1,
            fromWarehouseId: $asset->warehouseId,
            toWarehouseId: $toWarehouseId,
            performedBy: $initiatedBy,
            notes: $reason
        );

        foreach ($movements as $movement) {
            $this->stockMovementRepository->save($movement);
        }

        $asset->transfer($toWarehouseId, $asset->location);
        $this->assetRepository->save($asset);

        return $movements;
    }

    public function returnAsset(Uuid $assetId, Uuid $returnedBy, Uuid $receivedBy, string $condition): Asset
    {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $asset->return();
        $asset->updateCondition(\Src\Domain\Inventory\ValueObjects\AssetCondition::from($condition));

        $this->assetRepository->save($asset);

        return $asset;
    }

    public function getAssetsByWarehouse(Uuid $warehouseId): array
    {
        return $this->assetRepository->findByWarehouse($warehouseId);
    }

    public function getAssetsByStatus(AssetStatus $status): array
    {
        return $this->assetRepository->findByStatus($status);
    }

    public function getAssetsByCategory(Uuid $categoryId): array
    {
        return $this->assetRepository->findByCategory($categoryId);
    }

    public function getLowStockItems(Uuid $warehouseId): array
    {
        return $this->inventoryRepository->findLowStock($warehouseId);
    }

    public function getExpiringWarrantyItems(int $daysThreshold = 30): array
    {
        return $this->assetRepository->findExpiringWarranty($daysThreshold);
    }

    public function calculateTotalAssetValue(?Uuid $warehouseId = null): float
    {
        $assets = $warehouseId 
            ? $this->assetRepository->findByWarehouse($warehouseId)
            : $this->assetRepository->findAll();

        return array_reduce(
            $assets,
            fn(float $total, Asset $asset) => $total + $asset->purchasePrice,
            0.0
        );
    }
}
